<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PredictionHistory extends Model
{
    protected $table = 'prediction_history';

    protected $fillable = [
        'project_id',
        'completion_probability',
        'completion_percentage',
        'risk_level',
        'features',
        'feature_descriptions',
        'recommendations',
        'prediction_method',
        'model_version',
        'model_confidence',
        'execution_time_ms',
        'used_cache',
        'model_info',
        'project_metadata',
        'triggered_by',
        'user_id',
    ];

    protected $casts = [
        'features' => 'array',
        'feature_descriptions' => 'array',
        'recommendations' => 'array',
        'model_info' => 'array',
        'project_metadata' => 'array',
        'completion_probability' => 'float',
        'model_confidence' => 'float',
        'used_cache' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the project that owns this prediction
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who triggered this prediction
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get recent predictions
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to get predictions by risk level
     */
    public function scopeByRiskLevel($query, string $riskLevel)
    {
        return $query->where('risk_level', $riskLevel);
    }

    /**
     * Scope to get predictions for a specific project
     */
    public function scopeForProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Get the probability change from the previous prediction
     */
    public function getProbabilityChangeAttribute(): ?float
    {
        $previousPrediction = static::where('project_id', $this->project_id)
            ->where('created_at', '<', $this->created_at)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$previousPrediction) {
            return null;
        }

        return $this->completion_probability - $previousPrediction->completion_probability;
    }

    /**
     * Get the risk level change from the previous prediction
     */
    public function getRiskLevelChangeAttribute(): ?string
    {
        $previousPrediction = static::where('project_id', $this->project_id)
            ->where('created_at', '<', $this->created_at)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$previousPrediction) {
            return null;
        }

        if ($previousPrediction->risk_level === $this->risk_level) {
            return 'unchanged';
        }

        $riskOrder = ['low', 'medium', 'high', 'critical'];
        $previousIndex = array_search($previousPrediction->risk_level, $riskOrder);
        $currentIndex = array_search($this->risk_level, $riskOrder);

        if ($currentIndex < $previousIndex) {
            return 'improved';
        } elseif ($currentIndex > $previousIndex) {
            return 'deteriorated';
        }

        return 'unchanged';
    }

    /**
     * Get a human-readable description of the prediction
     */
    public function getDescriptionAttribute(): string
    {
        return sprintf(
            'Project completion probability: %s%% (%s risk) - %s',
            $this->completion_percentage,
            ucfirst($this->risk_level),
            $this->created_at->format('M j, Y g:i A')
        );
    }

    /**
     * Get performance metrics for this prediction
     */
    public function getPerformanceMetricsAttribute(): array
    {
        return [
            'execution_time_ms' => $this->execution_time_ms,
            'used_cache' => $this->used_cache,
            'model_confidence' => $this->model_confidence,
            'prediction_method' => $this->prediction_method,
            'model_version' => $this->model_version,
        ];
    }

    /**
     * Create a new prediction history record
     */
    public static function createFromPrediction(
        Project $project,
        array $prediction,
        array $features,
        array $recommendations,
        ?User $user = null,
        string $triggeredBy = 'manual',
        ?int $executionTimeMs = null
    ): self {
        return static::create([
            'project_id' => $project->id,
            'completion_probability' => $prediction['probability'],
            'completion_percentage' => $prediction['percentage'],
            'risk_level' => $prediction['risk_level'],
            'features' => $features,
            'feature_descriptions' => $prediction['feature_descriptions'] ?? [],
            'recommendations' => $recommendations,
            'prediction_method' => 'bayesian_network',
            'model_version' => '1.0',
            'model_confidence' => $prediction['confidence'] ?? null,
            'execution_time_ms' => $executionTimeMs,
            'used_cache' => $prediction['used_cache'] ?? false,
            'model_info' => $prediction['model_info'] ?? null,
            'project_metadata' => [
                'total_tasks' => $project->tasks()->count(),
                'completed_tasks' => $project->tasks()->where('status', 'Approved')->count(),
                'team_size' => $project->group?->members()?->count() ?? 0,
                'project_age_days' => now()->diffInDays($project->created_at),
                'last_updated' => $project->updated_at,
            ],
            'triggered_by' => $triggeredBy,
            'user_id' => $user?->id,
        ]);
    }

    /**
     * Get prediction trends for a project
     */
    public static function getTrends(int $projectId, int $days = 30): array
    {
        $predictions = static::forProject($projectId)
            ->recent($days)
            ->orderBy('created_at')
            ->get();

        if ($predictions->isEmpty()) {
            return [];
        }

        return [
            'trend_direction' => static::calculateTrendDirection($predictions),
            'average_probability' => $predictions->avg('completion_probability'),
            'latest_probability' => $predictions->last()->completion_probability,
            'probability_range' => [
                'min' => $predictions->min('completion_probability'),
                'max' => $predictions->max('completion_probability'),
            ],
            'risk_changes' => static::calculateRiskChanges($predictions),
            'prediction_count' => $predictions->count(),
        ];
    }

    /**
     * Calculate trend direction from predictions
     */
    private static function calculateTrendDirection($predictions): string
    {
        if ($predictions->count() < 2) {
            return 'insufficient_data';
        }

        $first = $predictions->first()->completion_probability;
        $last = $predictions->last()->completion_probability;
        $change = $last - $first;

        if (abs($change) < 0.05) {
            return 'stable';
        }

        return $change > 0 ? 'improving' : 'declining';
    }

    /**
     * Calculate risk level changes
     */
    private static function calculateRiskChanges($predictions): array
    {
        $changes = [];
        $previous = null;

        foreach ($predictions as $prediction) {
            if ($previous) {
                $changes[] = [
                    'from' => $previous->risk_level,
                    'to' => $prediction->risk_level,
                    'change' => $prediction->risk_level_change,
                    'date' => $prediction->created_at->toDateString(),
                ];
            }
            $previous = $prediction;
        }

        return $changes;
    }
}
