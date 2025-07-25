<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Activity extends Model
{
    use HasFactory;

    protected $table = 'activities';

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->user_id = Auth::id();
        });
    }

    protected $fillable = [
        'title',
        'user_id', // the user who created the activity
        'description',
        'start_date',
        'end_date',
        'priority', // low, medium, high, urgent
        'is_flexible', // can be rescheduled automatically
        'category', //  class, meeting, event, defense, presentation, evaluation, other
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_flexible' => 'boolean',
        'priority' => 'integer',
        'user_id' => 'integer',
    ];

    // Priority levels
    const PRIORITY_LOW = 1;
    const PRIORITY_MEDIUM = 2;
    const PRIORITY_HIGH = 3;
    const PRIORITY_URGENT = 4;

    public static function getPriorityOptions(): array
    {
        return [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_URGENT => 'Urgent',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getCategoryOptions(): array
    {
        return [
            'class' => 'Class',
            'meeting' => 'Meeting',
            'event' => 'Event',
            'defense' => 'Defense',
            'presentation' => 'Presentation',
            'evaluation' => 'Evaluation',
            'other' => 'Other',
        ];
    }

    public function getPriorityLabelAttribute(): string
    {
        return self::getPriorityOptions()[$this->priority] ?? 'Unknown';
    }

    public function getDurationAttribute(): int
    {
        return $this->start_date->diffInMinutes($this->end_date);
    }

    /**
     * Check if a date falls on a weekend
     */
    public static function isWeekend(Carbon $date): bool
    {
        return $date->isWeekend();
    }

    /**
     * Check if this activity conflicts with another activity
     */
    public function conflictsWith(Activity $other): bool
    {
        if (!$this->end_date || !$other->end_date) {
            return false;
        }

        return $this->start_date < $other->end_date && $this->end_date > $other->start_date;
    }

    /**
     * Find conflicting activities for a given time slot
     */
    public static function findConflicts(Carbon $startDate, Carbon $endDate, ?int $excludeId = null): Builder
    {
        $query = self::where(function ($q) use ($startDate, $endDate) {
            $q->where(function ($subQ) use ($startDate, $endDate) {
                // Activity starts before our end and ends after our start
                $subQ->where('start_date', '<', $endDate)
                    ->where('end_date', '>', $startDate)
                    ->whereNotNull('start_date')
                    ->whereNotNull('end_date');
            });
        });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query;
    }

    /**
     * Find the best available time slot for an activity
     */
    public static function findBestTimeSlot(Carbon $preferredStart, int $duration, int $priority = self::PRIORITY_MEDIUM): ?Carbon
    {
        $preferredEnd = $preferredStart->copy()->addMinutes($duration);

        // Check if preferred slot is available and not on weekend
        if (!$preferredStart->isWeekend()) {
            $conflicts = self::findConflicts($preferredStart, $preferredEnd)->get();

            if ($conflicts->isEmpty()) {
                return $preferredStart;
            }
        }

        // If there are conflicts or it's a weekend, try to find alternative slots
        $alternatives = [];

        // Try slots before the preferred time
        for ($i = 1; $i <= 72; $i++) { // Check 72 hours before (3 days)
            $altStart = $preferredStart->copy()->subHours($i);
            $altEnd = $altStart->copy()->addMinutes($duration);

            // Skip weekends
            if ($altStart->isWeekend()) {
                continue;
            }

            if (self::findConflicts($altStart, $altEnd)->count() === 0) {
                $alternatives[] = $altStart;
                break;
            }
        }

        // Try slots after the preferred time
        for ($i = 1; $i <= 72; $i++) { // Check 72 hours after (3 days)
            $altStart = $preferredStart->copy()->addHours($i);
            $altEnd = $altStart->copy()->addMinutes($duration);

            // Skip weekends
            if ($altStart->isWeekend()) {
                continue;
            }

            if (self::findConflicts($altStart, $altEnd)->count() === 0) {
                $alternatives[] = $altStart;
                break;
            }
        }

        // Return the closest alternative or null if none found
        return $alternatives[0] ?? null;
    }

    /**
     * Automatically reschedule lower priority conflicting activities
     */
    public function resolveConflicts(): array
    {
        $rescheduled = [];

        if (!$this->end_date) {
            return $rescheduled;
        }

        $conflicts = self::findConflicts($this->start_date, $this->end_date, $this->id)
            ->where('priority', '<', $this->priority)
            ->where('is_flexible', true)
            ->get();

        foreach ($conflicts as $conflict) {
            $newSlot = self::findBestTimeSlot(
                $conflict->start_date,
                $conflict->duration,
                $conflict->priority
            );

            if ($newSlot) {
                $oldDate = $conflict->start_date->copy();
                $conflict->update([
                    'start_date' => $newSlot,
                    'end_date' => $newSlot->copy()->addMinutes($conflict->duration),
                ]);

                $rescheduled[] = [
                    'activity' => $conflict,
                    'old_date' => $oldDate,
                    'new_date' => $newSlot,
                ];
            }
        }

        return $rescheduled;
    }

    /**
     * Scope for high priority activities
     */
    public function scopeHighPriority(Builder $query): Builder
    {
        return $query->where('priority', '>=', self::PRIORITY_HIGH);
    }

    /**
     * Scope for flexible activities
     */
    public function scopeFlexible(Builder $query): Builder
    {
        return $query->where('is_flexible', true);
    }

    /**
     * Scope for activities within a date range
     */
    public function scopeWithinDateRange(Builder $query, Carbon $start, Carbon $end): Builder
    {
        return $query->where('start_date', '>=', $start)
            ->where('end_date', '<=', $end);
    }
}
