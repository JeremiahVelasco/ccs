<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\ProjectPredictionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class ProjectPredictionController extends Controller
{
    public function __construct(
        private ProjectPredictionService $predictionService
    ) {}

    /**
     * Get prediction for a specific project
     */
    public function predict(Request $request, Project $project)
    {
        // Rate limiting
        $executed = RateLimiter::attempt(
            'predict-project:' . $request->user()->id,
            $perMinute = 10,
            function () {
                // Rate limit logic goes here
            }
        );

        if (!$executed) {
            return response()->json([
                'error' => 'Too many prediction requests. Please try again later.'
            ], 429);
        }

        try {
            // Check if user has access to this project
            if (!$request->user()->can('view', $project)) {
                return response()->json([
                    'error' => 'Unauthorized access to project'
                ], 403);
            }

            // Validate project state
            if (!$project->exists || !$project->group) {
                return response()->json([
                    'error' => 'Invalid project or missing group information'
                ], 400);
            }

            $prediction = $this->predictionService->predictCompletion($project);
            $recommendations = $this->predictionService->getRecommendations($project);

            return response()->json([
                'success' => true,
                'project_id' => $project->id,
                'project_name' => $project->title,
                'prediction' => [
                    'probability' => $prediction['probability'],
                    'percentage' => $prediction['percentage'],
                    'risk_level' => $prediction['risk_level'],
                    'last_updated' => $project->last_prediction_at?->toISOString(),
                    'is_cached' => isset($prediction['updated_at']) && $prediction['updated_at']->diffInMinutes(now()) < 30,
                ],
                'features' => $prediction['features'] ?? [],
                'recommendations' => $recommendations,
                'feature_descriptions' => $this->getFeatureDescriptions($prediction['features'] ?? []),
                'metadata' => [
                    'total_tasks' => $project->tasks()->count(),
                    'completed_tasks' => $project->tasks()->where('status', 'Approved')->count(),
                    'team_size' => $project->group->members()->count(),
                    'project_age_days' => now()->diffInDays($project->created_at),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Prediction request failed', [
                'project_id' => $project->id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate prediction',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get predictions for all user's projects
     */
    public function bulkPredict(Request $request)
    {
        // Rate limiting for bulk operations
        $executed = RateLimiter::attempt(
            'bulk-predict:' . $request->user()->id,
            $perMinute = 2,
            function () {
                // Rate limit logic goes here
            }
        );

        if (!$executed) {
            return response()->json([
                'error' => 'Too many bulk prediction requests. Please try again later.'
            ], 429);
        }

        try {
            // Validate request
            $request->validate([
                'limit' => 'sometimes|integer|min:1|max:50',
                'include_details' => 'sometimes|boolean',
            ]);

            $limit = $request->input('limit', 20);
            $includeDetails = $request->input('include_details', false);

            // Get user's projects efficiently
            $projects = $request->user()->projects()
                ->with(['tasks', 'group.members'])
                ->limit($limit)
                ->get();

            $predictions = collect();
            $failed = collect();

            foreach ($projects as $project) {
                try {
                    $prediction = $this->predictionService->predictCompletion($project);

                    $projectData = [
                        'project_id' => $project->id,
                        'project_name' => $project->title,
                        'probability' => $prediction['probability'],
                        'percentage' => $prediction['percentage'],
                        'risk_level' => $prediction['risk_level'],
                        'last_updated' => $project->last_prediction_at?->toISOString(),
                    ];

                    if ($includeDetails) {
                        $projectData['features'] = $prediction['features'] ?? [];
                        $projectData['recommendations'] = $this->predictionService->getRecommendations($project);
                        $projectData['feature_descriptions'] = $this->getFeatureDescriptions($prediction['features'] ?? []);
                    }

                    $predictions->push($projectData);
                } catch (\Exception $e) {
                    $failed->push([
                        'project_id' => $project->id,
                        'project_name' => $project->title,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'predictions' => $predictions,
                'failed' => $failed,
                'summary' => [
                    'total_projects' => $projects->count(),
                    'successful_predictions' => $predictions->count(),
                    'failed_predictions' => $failed->count(),
                    'risk_distribution' => [
                        'low_risk' => $predictions->where('risk_level', 'low')->count(),
                        'medium_risk' => $predictions->where('risk_level', 'medium')->count(),
                        'high_risk' => $predictions->where('risk_level', 'high')->count(),
                        'critical_risk' => $predictions->where('risk_level', 'critical')->count(),
                    ],
                    'average_completion_probability' => $predictions->avg('probability'),
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Bulk prediction failed', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate bulk predictions',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Refresh prediction cache for a project
     */
    public function refreshPrediction(Request $request, Project $project)
    {
        try {
            // Check authorization
            if (!$request->user()->can('view', $project)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Clear cache and regenerate
            $this->predictionService->clearPredictionCache($project->id);
            $prediction = $this->predictionService->predictCompletion($project);

            return response()->json([
                'success' => true,
                'message' => 'Prediction refreshed successfully',
                'prediction' => [
                    'probability' => $prediction['probability'],
                    'percentage' => $prediction['percentage'],
                    'risk_level' => $prediction['risk_level'],
                    'updated_at' => now()->toISOString(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Prediction refresh failed', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to refresh prediction'
            ], 500);
        }
    }

    /**
     * Get prediction history for a project
     */
    public function getPredictionHistory(Request $request, Project $project)
    {
        try {
            // Check authorization
            if (!$request->user()->can('view', $project)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $history = $this->predictionService->getPredictionHistory($project);

            return response()->json([
                'success' => true,
                'project_id' => $project->id,
                'history' => $history
            ]);
        } catch (\Exception $e) {
            Log::error('Prediction history request failed', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve prediction history'
            ], 500);
        }
    }

    /**
     * Get system health and statistics
     */
    public function getSystemHealth(Request $request)
    {
        try {
            // Check admin access
            if (!$request->user()->can('viewAny', Project::class)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $stats = [
                'total_projects' => Project::count(),
                'projects_with_predictions' => Project::whereNotNull('completion_probability')->count(),
                'cache_hit_rate' => $this->calculateCacheHitRate(),
                'python_script_health' => $this->checkPythonScriptHealth(),
                'recent_predictions' => Project::whereNotNull('last_prediction_at')
                    ->where('last_prediction_at', '>', now()->subHours(24))
                    ->count(),
            ];

            return response()->json([
                'success' => true,
                'health' => 'healthy',
                'stats' => $stats,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('System health check failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'health' => 'unhealthy',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get feature descriptions for the UI
     */
    private function getFeatureDescriptions(array $features): array
    {
        $descriptions = [];

        if (isset($features['task_progress'])) {
            $descriptions['task_progress'] = ['Low', 'Medium', 'High'][$features['task_progress'] ?? 0];
        }

        if (isset($features['team_collaboration'])) {
            $descriptions['team_collaboration'] = ['Poor', 'Good', 'Excellent'][$features['team_collaboration'] ?? 0];
        }

        if (isset($features['faculty_approval'])) {
            $descriptions['faculty_approval'] = $features['faculty_approval'] ? 'Approved' : 'Pending';
        }

        if (isset($features['timeline_adherence'])) {
            $descriptions['timeline_adherence'] = ['Behind', 'On Track', 'Ahead'][$features['timeline_adherence'] ?? 0];
        }

        return $descriptions;
    }

    /**
     * Calculate cache hit rate (mock implementation)
     */
    private function calculateCacheHitRate(): float
    {
        // This would require proper cache statistics
        // For now, return a mock value
        return 0.75; // 75% cache hit rate
    }

    /**
     * Check Python script health
     */
    private function checkPythonScriptHealth(): string
    {
        try {
            $testFeatures = [
                'task_progress' => 1,
                'team_collaboration' => 1,
                'faculty_approval' => 1,
                'timeline_adherence' => 1
            ];

            $command = escapeshellcmd(config('bayesian.python_executable', 'python3')) . ' ' .
                escapeshellarg(storage_path('scripts/bayesian_predictor.py')) . ' ' .
                escapeshellarg(json_encode($testFeatures));

            $output = shell_exec($command);
            $result = json_decode($output, true);

            return ($result && $result['success']) ? 'healthy' : 'unhealthy';
        } catch (\Exception $e) {
            return 'unhealthy';
        }
    }
}
