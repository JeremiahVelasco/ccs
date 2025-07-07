<?php

namespace App\Services;

use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PrioritySchedulerService
{
    /**
     * Schedule a new activity with conflict resolution
     */
    public function scheduleActivity(array $data): array
    {
        $startDate = Carbon::parse($data['date']);
        $duration = $data['duration'] ?? 60; // Default 1 hour
        $priority = $data['priority'] ?? Activity::PRIORITY_MEDIUM;
        $endDate = $startDate->copy()->addMinutes($duration);

        // Check for conflicts
        $conflicts = Activity::findConflicts($startDate, $endDate)->get();

        $result = [
            'success' => false,
            'activity' => null,
            'conflicts' => $conflicts,
            'rescheduled' => [],
            'suggested_times' => [],
            'message' => ''
        ];

        if ($conflicts->isEmpty()) {
            // No conflicts, create the activity
            $activity = Activity::create(array_merge($data, [
                'end_date' => $endDate
            ]));

            $result['success'] = true;
            $result['activity'] = $activity;
            $result['message'] = 'Activity scheduled successfully.';

            return $result;
        }

        // Handle conflicts based on priority
        $highPriorityConflicts = $conflicts->where('priority', '>=', $priority);
        $lowerPriorityConflicts = $conflicts->where('priority', '<', $priority);

        if ($highPriorityConflicts->isNotEmpty()) {
            // Cannot override higher priority activities
            $suggestedTimes = $this->findAlternativeSlots($startDate, $duration, 5);

            $result['suggested_times'] = $suggestedTimes;
            $result['message'] = 'Conflicts with higher priority activities. Here are some alternative time slots.';

            return $result;
        }

        // Can potentially reschedule lower priority activities
        if ($lowerPriorityConflicts->isNotEmpty()) {
            $flexibleConflicts = $lowerPriorityConflicts->where('is_flexible', true);
            $inflexibleConflicts = $lowerPriorityConflicts->where('is_flexible', false);

            if ($inflexibleConflicts->isNotEmpty()) {
                // Cannot reschedule inflexible activities
                $suggestedTimes = $this->findAlternativeSlots($startDate, $duration, 5);

                $result['suggested_times'] = $suggestedTimes;
                $result['message'] = 'Conflicts with inflexible activities. Here are some alternative time slots.';

                return $result;
            }

            // Try to reschedule flexible conflicts
            $rescheduled = $this->rescheduleConflicts($flexibleConflicts, $startDate, $endDate);

            if (count($rescheduled) === $flexibleConflicts->count()) {
                // Successfully rescheduled all conflicts
                $activity = Activity::create(array_merge($data, [
                    'end_date' => $endDate
                ]));

                $result['success'] = true;
                $result['activity'] = $activity;
                $result['rescheduled'] = $rescheduled;
                $result['message'] = 'Activity scheduled successfully. ' . count($rescheduled) . ' activities were rescheduled.';

                return $result;
            } else {
                // Could not reschedule some conflicts
                $suggestedTimes = $this->findAlternativeSlots($startDate, $duration, 5);

                $result['suggested_times'] = $suggestedTimes;
                $result['message'] = 'Some conflicts could not be resolved. Here are some alternative time slots.';

                return $result;
            }
        }

        return $result;
    }

    /**
     * Find alternative time slots
     */
    private function findAlternativeSlots(Carbon $preferredStart, int $duration, int $maxSuggestions = 5): array
    {
        $suggestions = [];
        $currentDate = $preferredStart->copy();

        // Search for available slots in the next 7 days
        for ($day = 0; $day < 7 && count($suggestions) < $maxSuggestions; $day++) {
            $dayStart = $currentDate->copy()->startOfDay()->addHours(8); // Start from 8 AM
            $dayEnd = $currentDate->copy()->endOfDay()->subHours(2); // End at 10 PM

            // Check hourly slots
            for ($hour = 0; $hour < 14; $hour++) { // 8 AM to 10 PM = 14 hours
                $slotStart = $dayStart->copy()->addHours($hour);
                $slotEnd = $slotStart->copy()->addMinutes($duration);

                if ($slotEnd > $dayEnd) {
                    break;
                }

                $conflicts = Activity::findConflicts($slotStart, $slotEnd)->count();

                if ($conflicts === 0) {
                    $suggestions[] = [
                        'start' => $slotStart,
                        'end' => $slotEnd,
                        'day' => $slotStart->format('l'),
                        'time' => $slotStart->format('g:i A'),
                    ];

                    if (count($suggestions) >= $maxSuggestions) {
                        break 2;
                    }
                }
            }

            $currentDate->addDay();
        }

        return $suggestions;
    }

    /**
     * Reschedule conflicting activities
     */
    private function rescheduleConflicts(Collection $conflicts, Carbon $newStart, Carbon $newEnd): array
    {
        $rescheduled = [];

        foreach ($conflicts as $conflict) {
            $newSlot = Activity::findBestTimeSlot(
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
     * Get daily schedule optimization suggestions
     */
    public function optimizeDailySchedule(Carbon $date): array
    {
        $activities = Activity::whereDate('date', $date)
            ->orderBy('priority', 'desc')
            ->orderBy('date', 'asc')
            ->get();

        $suggestions = [];
        $conflicts = [];

        foreach ($activities as $activity) {
            if (!$activity->end_date) {
                continue;
            }

            $activityConflicts = Activity::findConflicts(
                $activity->date,
                $activity->end_date,
                $activity->id
            )->whereDate('date', $date)->get();

            if ($activityConflicts->isNotEmpty()) {
                $conflicts[] = [
                    'activity' => $activity,
                    'conflicts' => $activityConflicts
                ];
            }
        }

        // Generate suggestions for resolving conflicts
        foreach ($conflicts as $conflict) {
            $activity = $conflict['activity'];
            $conflictingActivities = $conflict['conflicts'];

            // Check if we can reschedule lower priority activities
            $lowerPriorityConflicts = $conflictingActivities->where('priority', '<', $activity->priority);

            if ($lowerPriorityConflicts->isNotEmpty()) {
                $suggestions[] = [
                    'type' => 'reschedule_lower_priority',
                    'primary_activity' => $activity,
                    'activities_to_reschedule' => $lowerPriorityConflicts,
                    'message' => "Consider rescheduling {$lowerPriorityConflicts->count()} lower priority activities to accommodate '{$activity->title}'"
                ];
            }
        }

        return [
            'date' => $date,
            'total_activities' => $activities->count(),
            'conflicts' => $conflicts,
            'suggestions' => $suggestions
        ];
    }
}
