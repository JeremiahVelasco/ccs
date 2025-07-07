<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Activity extends Model
{
    use HasFactory;

    protected $table = 'activities';

    protected $fillable = [
        'title',
        'description',
        'date',
        'end_date',
        'priority',
        'duration', // in minutes
        'is_flexible', // can be rescheduled automatically
        'category',
    ];

    protected $casts = [
        'date' => 'datetime',
        'end_date' => 'datetime',
        'is_flexible' => 'boolean',
        'priority' => 'integer',
        'duration' => 'integer',
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

    public function getPriorityLabelAttribute(): string
    {
        return self::getPriorityOptions()[$this->priority] ?? 'Unknown';
    }

    public function getEndDateAttribute($value): ?Carbon
    {
        if ($value) {
            return Carbon::parse($value);
        }

        // If no end_date is set, calculate from start date + duration
        if ($this->date && $this->duration) {
            return $this->date->addMinutes($this->duration);
        }

        return null;
    }

    /**
     * Check if this activity conflicts with another activity
     */
    public function conflictsWith(Activity $other): bool
    {
        if (!$this->end_date || !$other->end_date) {
            return false;
        }

        return $this->date < $other->end_date && $this->end_date > $other->date;
    }

    /**
     * Find conflicting activities for a given time slot
     */
    public static function findConflicts(Carbon $startDate, Carbon $endDate, ?int $excludeId = null): Builder
    {
        $query = self::where(function ($q) use ($startDate, $endDate) {
            $q->where(function ($subQ) use ($startDate, $endDate) {
                // Activity starts before our end and ends after our start
                $subQ->where('date', '<', $endDate)
                    ->where(function ($endQ) use ($startDate) {
                        $endQ->where('end_date', '>', $startDate)
                            ->orWhereRaw('DATE_ADD(date, INTERVAL duration MINUTE) > ?', [$startDate]);
                    });
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

        // Check if preferred slot is available
        $conflicts = self::findConflicts($preferredStart, $preferredEnd)->get();

        if ($conflicts->isEmpty()) {
            return $preferredStart;
        }

        // If there are conflicts, try to find alternative slots
        $alternatives = [];

        // Try slots before the preferred time
        for ($i = 1; $i <= 24; $i++) { // Check 24 hours before
            $altStart = $preferredStart->copy()->subHours($i);
            $altEnd = $altStart->copy()->addMinutes($duration);

            if (self::findConflicts($altStart, $altEnd)->count() === 0) {
                $alternatives[] = $altStart;
                break;
            }
        }

        // Try slots after the preferred time
        for ($i = 1; $i <= 24; $i++) { // Check 24 hours after
            $altStart = $preferredStart->copy()->addHours($i);
            $altEnd = $altStart->copy()->addMinutes($duration);

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

        $conflicts = self::findConflicts($this->date, $this->end_date, $this->id)
            ->where('priority', '<', $this->priority)
            ->where('is_flexible', true)
            ->get();

        foreach ($conflicts as $conflict) {
            $newSlot = self::findBestTimeSlot(
                $conflict->date,
                $conflict->duration,
                $conflict->priority
            );

            if ($newSlot) {
                $oldDate = $conflict->date->copy();
                $conflict->update([
                    'date' => $newSlot,
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
        return $query->where('date', '>=', $start)
            ->where('date', '<=', $end);
    }
}
