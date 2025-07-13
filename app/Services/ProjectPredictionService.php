<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\PredictionHistory;

class ProjectPredictionService
{
    private string $pythonScriptPath;
    private int $cacheTimeMinutes;
    private string $pythonExecutable;

    public function __construct()
    {
        $this->pythonScriptPath = storage_path('scripts/bayesian_predictor.py');
        $this->cacheTimeMinutes = config('bayesian.cache_time_minutes', 30);
        $this->pythonExecutable = config('bayesian.python_executable', 'python3');
    }

    /**
     * Calculate project features based on database data
     */
    public function calculateProjectFeatures($project)
    {
        // Validate input
        if (!$project || !$project->exists) {
            throw new \InvalidArgumentException('Invalid project provided');
        }

        // Calculate task progress (0: Low, 1: Medium, 2: High)
        $totalTasks = $project->tasks()->count();
        $completedTasks = $project->tasks()->whereIn('status', ['Approved', 'Done'])->count();
        $taskProgress = $totalTasks > 0 ? $completedTasks / $totalTasks : 0;

        $taskProgressLevel = match (true) {
            $taskProgress >= 0.8 => 2, // High
            $taskProgress >= 0.4 => 1, // Medium
            default => 0 // Low
        };

        // Calculate team collaboration based on activity
        $teamSize = $project->group?->members()?->count() ?? 1;

        // Fixed: Use correct field name and improved collaboration calculation
        $recentActivities = $project->tasks()
            ->where('is_faculty_approved', true)
            ->where('created_at', '>=', now()->subWeeks(2))
            ->count();

        // Alternative: Include all recent task activities
        $recentTaskUpdates = $project->tasks()
            ->where('updated_at', '>=', now()->subWeeks(2))
            ->count();

        $collaborationScore = $teamSize > 0 ? min(($recentActivities + $recentTaskUpdates) / ($teamSize * 5), 1) : 0;
        $teamCollaboration = match (true) {
            $collaborationScore >= 0.7 => 2, // Excellent
            $collaborationScore >= 0.4 => 1, // Good
            default => 0 // Poor
        };

        // Fixed: Faculty approval status - use correct field name
        $approvedTasks = $project->tasks()
            ->where('is_faculty_approved', true)
            ->count();
        $facultyApproval = ($totalTasks > 0 && $approvedTasks / $totalTasks >= 0.5) ? 1 : 0;

        // Fixed: Timeline adherence calculation - handle missing deadline gracefully
        $timelineAdherence = $this->calculateTimelineAdherence($project, $taskProgress);

        return [
            'task_progress' => $taskProgressLevel,
            'team_collaboration' => $teamCollaboration,
            'faculty_approval' => $facultyApproval,
            'timeline_adherence' => $timelineAdherence
        ];
    }

    /**
     * Calculate timeline adherence safely
     */
    private function calculateTimelineAdherence($project, $taskProgress): int
    {
        // Check if project has any task deadlines
        $upcomingDeadlines = $project->tasks()
            ->whereNotNull('deadline')
            ->where('deadline', '>', now())
            ->count();

        $overdueTasks = $project->tasks()
            ->whereNotNull('deadline')
            ->where('deadline', '<', now())
            ->where('status', '!=', 'Approved')
            ->count();

        $totalTasksWithDeadlines = $project->tasks()
            ->whereNotNull('deadline')
            ->count();

        // If no deadlines set, estimate based on project age and progress
        if ($totalTasksWithDeadlines === 0) {
            $projectAge = now()->diffInDays($project->created_at);
            $expectedProgressByAge = min($projectAge / 90, 1); // Assume 90-day project cycle

            return match (true) {
                $taskProgress > $expectedProgressByAge + 0.2 => 2, // Ahead
                $taskProgress >= $expectedProgressByAge - 0.1 => 1, // On track
                default => 0 // Behind
            };
        }

        // Calculate based on actual deadlines
        $onTimePercentage = $totalTasksWithDeadlines > 0 ?
            ($totalTasksWithDeadlines - $overdueTasks) / $totalTasksWithDeadlines : 0;

        return match (true) {
            $onTimePercentage >= 0.8 => 2, // Ahead
            $onTimePercentage >= 0.6 => 1, // On track
            default => 0 // Behind
        };
    }

    /**
     * Predict project completion probability with caching
     */
    public function predictCompletion($project)
    {
        // Check cache first
        $cacheKey = "project_prediction_{$project->id}";
        $cachedPrediction = Cache::get($cacheKey);

        if (
            $cachedPrediction && $project->last_prediction_at &&
            $project->last_prediction_at->diffInMinutes(now()) < $this->cacheTimeMinutes
        ) {
            return $cachedPrediction;
        }

        try {
            $startTime = microtime(true);

            // Debug: Log the feature calculation
            Log::info('Starting prediction for project', ['project_id' => $project->id]);

            $features = $this->calculateProjectFeatures($project);
            Log::info('Calculated features', ['project_id' => $project->id, 'features' => $features]);

            $this->validateFeatures($features);

            // Call Python script with better error handling
            $result = $this->executePythonScript($features);
            Log::info('Python script result', ['project_id' => $project->id, 'result' => $result]);

            if (!$result || !$result['success']) {
                $errorMsg = $result['error'] ?? 'Unknown error from Python script';
                Log::error('Python script failed', ['project_id' => $project->id, 'error' => $errorMsg]);
                throw new \Exception($errorMsg);
            }

            $executionTimeMs = round((microtime(true) - $startTime) * 1000);

            // Store prediction in database for tracking
            $project->update([
                'completion_probability' => $result['completion_probability'],
                'last_prediction_at' => now(),
                'prediction_version' => ($project->prediction_version ?? 0) + 1
            ]);

            $prediction = [
                'probability' => $result['completion_probability'],
                'percentage' => $result['completion_percentage'],
                'risk_level' => $this->getRiskLevel($result['completion_probability']),
                'features' => $features,
                'updated_at' => now(),
                'execution_time_ms' => $executionTimeMs,
                'model_info' => $result['model_info'] ?? null,
                'used_cache' => false
            ];

            // Get recommendations
            $recommendations = $this->getRecommendations($project);

            // Save to prediction history
            try {
                $historyRecord = PredictionHistory::createFromPrediction(
                    $project,
                    $prediction,
                    $features,
                    $recommendations,
                    Auth::check() ? Auth::user() : null,
                    'manual',
                    $executionTimeMs
                );
                Log::info('Created prediction history record', ['project_id' => $project->id, 'history_id' => $historyRecord->id]);
            } catch (\Exception $historyError) {
                Log::error('Failed to save prediction history', [
                    'project_id' => $project->id,
                    'error' => $historyError->getMessage()
                ]);
                // Don't fail the entire prediction if history saving fails
            }

            // Cache the prediction
            Cache::put($cacheKey, $prediction, now()->addMinutes($this->cacheTimeMinutes));

            Log::info('Prediction completed successfully', [
                'project_id' => $project->id,
                'probability' => $prediction['probability'],
                'execution_time_ms' => $executionTimeMs
            ]);

            return $prediction;
        } catch (\Exception $e) {
            Log::error('Bayesian prediction failed: ' . $e->getMessage(), [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'features' => $features ?? 'not_calculated'
            ]);

            // Try to calculate features for fallback even if they failed before
            $fallbackFeatures = [];
            try {
                $fallbackFeatures = $this->calculateProjectFeatures($project);
            } catch (\Exception $featureError) {
                Log::error('Feature calculation also failed', [
                    'project_id' => $project->id,
                    'feature_error' => $featureError->getMessage()
                ]);
            }

            return [
                'probability' => 0.5,
                'percentage' => 50,
                'risk_level' => 'medium',
                'error' => 'Prediction unavailable: ' . $e->getMessage(),
                'features' => $fallbackFeatures,
                'debug_info' => [
                    'python_executable' => $this->pythonExecutable,
                    'script_path' => $this->pythonScriptPath,
                    'script_exists' => file_exists($this->pythonScriptPath)
                ]
            ];
        }
    }

    /**
     * Execute Python script with proper error handling
     */
    private function executePythonScript(array $features): array
    {
        $command = escapeshellcmd($this->pythonExecutable) . ' ' .
            escapeshellarg($this->pythonScriptPath) . ' ' .
            escapeshellarg(json_encode($features));

        // Debug: Log the command being executed
        Log::info('Executing Python command', [
            'command' => $command,
            'python_executable' => $this->pythonExecutable,
            'script_path' => $this->pythonScriptPath,
            'script_exists' => file_exists($this->pythonScriptPath),
            'features' => $features
        ]);

        // Add timeout and error handling
        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];

        $process = proc_open($command, $descriptorspec, $pipes);

        if (!is_resource($process)) {
            Log::error('Failed to create Python process');
            throw new \Exception('Failed to start Python process');
        }

        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $return_value = proc_close($process);

        // Debug: Log the raw output
        Log::info('Python script execution completed', [
            'return_value' => $return_value,
            'output' => $output,
            'error' => $error
        ]);

        if ($return_value !== 0) {
            Log::error('Python script returned non-zero exit code', [
                'return_value' => $return_value,
                'error' => $error,
                'output' => $output
            ]);

            // If Python fails due to dependencies, fall back to PHP calculation
            if (strpos($error, 'pgmpy') !== false || strpos($error, 'numpy') !== false || strpos($error, 'scipy') !== false) {
                Log::info('Python dependencies issue detected, falling back to PHP-based calculation');
                return $this->calculatePurePHPPrediction($features);
            }

            throw new \Exception('Python script failed with error: ' . $error);
        }

        if (!$output) {
            Log::error('No output from Python script', ['error' => $error]);
            throw new \Exception('No output from Python script. Error: ' . $error);
        }

        $result = json_decode(trim($output), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Invalid JSON from Python script', [
                'json_error' => json_last_error_msg(),
                'output' => $output
            ]);
            throw new \Exception('Invalid JSON response from Python script: ' . json_last_error_msg());
        }

        return $result;
    }

    /**
     * Pure PHP implementation of Bayesian-like prediction (fallback)
     */
    private function calculatePurePHPPrediction(array $features): array
    {
        Log::info('Using PHP-based prediction fallback', ['features' => $features]);

        // Extract feature values
        $taskProgress = $features['task_progress'] ?? 0;
        $teamCollaboration = $features['team_collaboration'] ?? 0;
        $facultyApproval = $features['faculty_approval'] ?? 0;
        $timelineAdherence = $features['timeline_adherence'] ?? 0;

        // Weighted scoring based on project management research
        $weights = [
            'task_progress' => 0.35,        // Most important
            'team_collaboration' => 0.25,
            'faculty_approval' => 0.25,
            'timeline_adherence' => 0.15
        ];

        // Normalize features to 0-1 scale
        $normalizedScores = [
            'task_progress' => $taskProgress / 2.0,
            'team_collaboration' => $teamCollaboration / 2.0,
            'faculty_approval' => $facultyApproval / 1.0,
            'timeline_adherence' => $timelineAdherence / 2.0
        ];

        // Calculate weighted average
        $baseScore = 0;
        foreach ($normalizedScores as $feature => $score) {
            $baseScore += $score * $weights[$feature];
        }

        // Apply Bayesian-like adjustments for feature combinations
        $adjustments = 0;

        // Positive combinations
        if ($taskProgress >= 2 && $facultyApproval >= 1) {
            $adjustments += 0.1; // High progress + approval boost
        }

        if ($teamCollaboration >= 2 && $timelineAdherence >= 1) {
            $adjustments += 0.08; // Good team + timeline boost
        }

        if ($taskProgress >= 1 && $teamCollaboration >= 1 && $facultyApproval >= 1) {
            $adjustments += 0.05; // All-around decent performance
        }

        // Negative combinations
        if ($taskProgress === 0 && $timelineAdherence === 0) {
            $adjustments -= 0.15; // Poor progress + behind schedule penalty
        }

        if ($teamCollaboration === 0 && $facultyApproval === 0) {
            $adjustments -= 0.1; // Poor collaboration + no approval penalty
        }

        // Final probability calculation
        $probability = max(0.05, min(0.95, $baseScore + $adjustments));

        // Add some realistic variance (small random factor)
        $variance = (mt_rand(-20, 20) / 1000); // ±0.02 variance
        $probability = max(0.05, min(0.95, $probability + $variance));

        $percentage = round($probability * 100, 2);

        Log::info('PHP prediction calculated', [
            'base_score' => $baseScore,
            'adjustments' => $adjustments,
            'final_probability' => $probability,
            'percentage' => $percentage
        ]);

        return [
            'success' => true,
            'completion_probability' => $probability,
            'completion_percentage' => $percentage,
            'model_info' => [
                'method' => 'php_weighted_scoring',
                'nodes' => 4,
                'edges' => 4,
                'inference_engine' => 'weighted_average_with_adjustments'
            ]
        ];
    }

    /**
     * Validate feature values
     */
    private function validateFeatures(array $features): void
    {
        $requiredFields = ['task_progress', 'team_collaboration', 'faculty_approval', 'timeline_adherence'];

        foreach ($requiredFields as $field) {
            if (!isset($features[$field])) {
                throw new \InvalidArgumentException("Missing required feature: {$field}");
            }
        }

        // Validate ranges
        if (!in_array($features['task_progress'], [0, 1, 2])) {
            throw new \InvalidArgumentException('task_progress must be 0, 1, or 2');
        }

        if (!in_array($features['team_collaboration'], [0, 1, 2])) {
            throw new \InvalidArgumentException('team_collaboration must be 0, 1, or 2');
        }

        if (!in_array($features['faculty_approval'], [0, 1])) {
            throw new \InvalidArgumentException('faculty_approval must be 0 or 1');
        }

        if (!in_array($features['timeline_adherence'], [0, 1, 2])) {
            throw new \InvalidArgumentException('timeline_adherence must be 0, 1, or 2');
        }
    }

    /**
     * Get risk level based on completion probability
     */
    private function getRiskLevel(float $probability): string
    {
        return match (true) {
            $probability >= 0.8 => 'low',
            $probability >= 0.6 => 'medium',
            $probability >= 0.4 => 'high',
            default => 'critical'
        };
    }

    /**
     * Get recommendations based on project features
     */
    public function getRecommendations($project): array
    {
        $features = $this->calculateProjectFeatures($project);
        $recommendations = [];

        if ($features['task_progress'] === 0) {
            $recommendations[] = 'Focus on completing more development and documentation tasks';
        }

        if ($features['team_collaboration'] === 0) {
            $recommendations[] = 'Increase team communication and collaborative activities';
        }

        if ($features['faculty_approval'] === 0) {
            $recommendations[] = 'Submit more tasks for faculty review and approval';
        }

        if ($features['timeline_adherence'] === 0) {
            $recommendations[] = 'Review project timeline and accelerate task completion';
        }

        // Add positive reinforcement
        if (empty($recommendations)) {
            $recommendations[] = 'Project is performing well! Keep up the good work and maintain consistency.';
        }

        return $recommendations;
    }

    /**
     * Clear prediction cache for a project
     */
    public function clearPredictionCache($projectId): void
    {
        $cacheKey = "project_prediction_{$projectId}";
        Cache::forget($cacheKey);
    }

    /**
     * Get prediction history for a project
     */
    public function getPredictionHistory($project): array
    {
        $currentPrediction = $this->predictCompletion($project);

        $history = $project->predictionHistory()
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $trends = PredictionHistory::getTrends($project->id);

        return [
            'current' => $currentPrediction,
            'history' => $history->map(function ($record) {
                return [
                    'id' => $record->id,
                    'probability' => $record->completion_probability,
                    'percentage' => $record->completion_percentage,
                    'risk_level' => $record->risk_level,
                    'features' => $record->features,
                    'recommendations' => $record->recommendations,
                    'created_at' => $record->created_at->toISOString(),
                    'probability_change' => $record->probability_change,
                    'risk_level_change' => $record->risk_level_change,
                    'execution_time_ms' => $record->execution_time_ms,
                    'triggered_by' => $record->triggered_by,
                ];
            }),
            'trends' => $trends,
            'statistics' => [
                'total_predictions' => $history->count(),
                'average_probability' => $history->avg('completion_probability'),
                'highest_probability' => $history->max('completion_probability'),
                'lowest_probability' => $history->min('completion_probability'),
                'most_recent' => $history->first()?->created_at?->toISOString(),
                'oldest' => $history->last()?->created_at?->toISOString(),
            ]
        ];
    }
}
