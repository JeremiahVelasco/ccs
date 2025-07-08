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
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);
        $priority = $data['priority'] ?? Activity::PRIORITY_MEDIUM;

        // Debug: Prepare debug information
        $debugInfo = [
            'new_activity' => [
                'title' => $data['title'] ?? 'Unknown',
                'start_date' => $startDate->format('Y-m-d H:i:s'),
                'end_date' => $endDate->format('Y-m-d H:i:s'),
                'priority' => $priority,
                'priority_label' => Activity::getPriorityOptions()[$priority] ?? 'Unknown'
            ],
            'conflicts_found' => [],
            'priority_analysis' => [],
            'decision_path' => []
        ];

        // Check for conflicts
        $conflicts = Activity::findConflicts($startDate, $endDate)->get();

        // Debug: Record all conflicts found
        $debugInfo['conflicts_found'] = [
            'count' => $conflicts->count(),
            'details' => $conflicts->map(function ($conflict) {
                return [
                    'id' => $conflict->id,
                    'title' => $conflict->title,
                    'start_date' => $conflict->start_date->format('Y-m-d H:i:s'),
                    'end_date' => $conflict->end_date->format('Y-m-d H:i:s'),
                    'priority' => $conflict->priority,
                    'priority_label' => $conflict->priority_label,
                    'is_flexible' => $conflict->is_flexible
                ];
            })->toArray()
        ];

        $result = [
            'success' => false,
            'activity' => null,
            'conflicts' => $conflicts,
            'rescheduled' => [],
            'suggested_times' => [],
            'message' => '',
            'debug_info' => $debugInfo
        ];

        if ($conflicts->isEmpty()) {
            $debugInfo['decision_path'][] = 'No conflicts found, creating activity';
            $result['debug_info'] = $debugInfo;

            $activity = Activity::create(array_merge($data, [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]));

            $result['success'] = true;
            $result['activity'] = $activity;
            $result['message'] = 'Activity scheduled successfully.';

            return $result;
        }

        // Handle conflicts based on priority
        $highPriorityConflicts = $conflicts->where('priority', '>', $priority);
        $samePriorityConflicts = $conflicts->where('priority', '=', $priority);
        $lowerPriorityConflicts = $conflicts->where('priority', '<', $priority);

        // Debug: Record priority analysis
        $debugInfo['priority_analysis'] = [
            'new_activity_priority' => $priority,
            'higher_priority_conflicts' => [
                'count' => $highPriorityConflicts->count(),
                'details' => $highPriorityConflicts->map(function ($conflict) {
                    return [
                        'id' => $conflict->id,
                        'title' => $conflict->title,
                        'priority' => $conflict->priority,
                        'priority_label' => $conflict->priority_label
                    ];
                })->toArray()
            ],
            'same_priority_conflicts' => [
                'count' => $samePriorityConflicts->count(),
                'details' => $samePriorityConflicts->map(function ($conflict) {
                    return [
                        'id' => $conflict->id,
                        'title' => $conflict->title,
                        'priority' => $conflict->priority,
                        'priority_label' => $conflict->priority_label,
                        'is_flexible' => $conflict->is_flexible
                    ];
                })->toArray()
            ],
            'lower_priority_conflicts' => [
                'count' => $lowerPriorityConflicts->count(),
                'details' => $lowerPriorityConflicts->map(function ($conflict) {
                    return [
                        'id' => $conflict->id,
                        'title' => $conflict->title,
                        'priority' => $conflict->priority,
                        'priority_label' => $conflict->priority_label,
                        'is_flexible' => $conflict->is_flexible
                    ];
                })->toArray()
            ]
        ];

        if ($highPriorityConflicts->isNotEmpty()) {
            $debugInfo['decision_path'][] = 'Found higher priority conflicts, suggesting alternative slots';

            // Cannot override higher priority activities
            $suggestedTimes = $this->findAlternativeSlots($startDate, $endDate, 5);

            $result['suggested_times'] = $suggestedTimes;
            $result['message'] = 'Conflicts with higher priority activities. Here are some alternative time slots.';
            $result['debug_info'] = $debugInfo;

            return $result;
        }

        if ($samePriorityConflicts->isNotEmpty()) {
            $debugInfo['decision_path'][] = 'Found same priority conflicts, analyzing flexibility';

            // Handle same priority conflicts - try to reschedule flexible ones
            $flexibleSamePriority = $samePriorityConflicts->where('is_flexible', true);
            $inflexibleSamePriority = $samePriorityConflicts->where('is_flexible', false);

            $debugInfo['decision_path'][] = 'Flexible same priority: ' . $flexibleSamePriority->count() . ', Inflexible same priority: ' . $inflexibleSamePriority->count();

            if ($inflexibleSamePriority->isNotEmpty()) {
                $debugInfo['decision_path'][] = 'Cannot reschedule inflexible same priority activities, suggesting alternatives';

                // Cannot reschedule inflexible activities of same priority
                $suggestedTimes = $this->findAlternativeSlots($startDate, $endDate, 5);

                $result['suggested_times'] = $suggestedTimes;
                $result['message'] = 'Conflicts with activities of the same priority that cannot be rescheduled. Here are some alternative time slots.';
                $result['debug_info'] = $debugInfo;

                return $result;
            }

            if ($flexibleSamePriority->isNotEmpty()) {
                $debugInfo['decision_path'][] = 'Attempting to reschedule flexible same priority conflicts';

                // Try to reschedule flexible same priority conflicts
                $rescheduled = $this->rescheduleConflicts($flexibleSamePriority, $startDate, $endDate);

                $debugInfo['decision_path'][] = 'Rescheduled ' . count($rescheduled) . ' out of ' . $flexibleSamePriority->count() . ' same priority conflicts';

                if (count($rescheduled) === $flexibleSamePriority->count()) {
                    $debugInfo['decision_path'][] = 'Successfully rescheduled all same priority conflicts, creating activity';

                    // Successfully rescheduled all same priority conflicts
                    $activity = Activity::create(array_merge($data, [
                        'start_date' => $startDate,
                        'end_date' => $endDate
                    ]));

                    $result['success'] = true;
                    $result['activity'] = $activity;
                    $result['rescheduled'] = $rescheduled;
                    $result['message'] = 'Activity scheduled successfully. ' . count($rescheduled) . ' activities of the same priority were rescheduled.';
                    $result['debug_info'] = $debugInfo;

                    return $result;
                } else {
                    $debugInfo['decision_path'][] = 'Could not reschedule all same priority conflicts, suggesting alternatives';

                    // Could not reschedule some same priority conflicts
                    $suggestedTimes = $this->findAlternativeSlots($startDate, $endDate, 5);

                    $result['suggested_times'] = $suggestedTimes;
                    $result['message'] = 'Some same priority conflicts could not be resolved. Here are some alternative time slots.';
                    $result['debug_info'] = $debugInfo;

                    return $result;
                }
            }
        }

        // Can potentially reschedule lower priority activities
        if ($lowerPriorityConflicts->isNotEmpty()) {
            $debugInfo['decision_path'][] = 'Found lower priority conflicts, analyzing flexibility';

            $flexibleConflicts = $lowerPriorityConflicts->where('is_flexible', true);
            $inflexibleConflicts = $lowerPriorityConflicts->where('is_flexible', false);

            $debugInfo['decision_path'][] = 'Flexible lower priority: ' . $flexibleConflicts->count() . ', Inflexible lower priority: ' . $inflexibleConflicts->count();

            if ($inflexibleConflicts->isNotEmpty()) {
                $debugInfo['decision_path'][] = 'Cannot reschedule inflexible lower priority activities, suggesting alternatives';

                // Cannot reschedule inflexible activities
                $suggestedTimes = $this->findAlternativeSlots($startDate, $endDate, 5);

                $result['suggested_times'] = $suggestedTimes;
                $result['message'] = 'Conflicts with inflexible activities. Here are some alternative time slots.';
                $result['debug_info'] = $debugInfo;

                return $result;
            }

            $debugInfo['decision_path'][] = 'Attempting to reschedule flexible lower priority conflicts';

            // Try to reschedule flexible conflicts
            $rescheduled = $this->rescheduleConflicts($flexibleConflicts, $startDate, $endDate);

            $debugInfo['decision_path'][] = 'Rescheduled ' . count($rescheduled) . ' out of ' . $flexibleConflicts->count() . ' lower priority conflicts';

            if (count($rescheduled) === $flexibleConflicts->count()) {
                $debugInfo['decision_path'][] = 'Successfully rescheduled all lower priority conflicts, creating activity';

                // Successfully rescheduled all conflicts
                $activity = Activity::create(array_merge($data, [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]));

                $result['success'] = true;
                $result['activity'] = $activity;
                $result['rescheduled'] = $rescheduled;
                $result['message'] = 'Activity scheduled successfully. ' . count($rescheduled) . ' activities were rescheduled.';
                $result['debug_info'] = $debugInfo;

                return $result;
            } else {
                $debugInfo['decision_path'][] = 'Could not reschedule all lower priority conflicts, suggesting alternatives';

                // Could not reschedule some conflicts
                $suggestedTimes = $this->findAlternativeSlots($startDate, $endDate, 5);

                $result['suggested_times'] = $suggestedTimes;
                $result['message'] = 'Some conflicts could not be resolved. Here are some alternative time slots.';
                $result['debug_info'] = $debugInfo;

                return $result;
            }
        }

        $debugInfo['decision_path'][] = 'No conflicts found after analysis, but this should not happen';
        $result['debug_info'] = $debugInfo;

        return $result;
    }

    /**
     * Find alternative time slots
     */
    private function findAlternativeSlots(Carbon $preferredStart, Carbon $endDate, int $maxSuggestions = 3): array
    {
        $suggestions = [];
        $currentDate = $preferredStart->copy();

        // Calculate the duration of the original activity
        $durationInMinutes = $preferredStart->diffInMinutes($endDate);

        // Search for available slots in the next 7 days
        for ($day = 0; $day < 7 && count($suggestions) < $maxSuggestions; $day++) {
            $dayStart = $currentDate->copy()->startOfDay()->addHours(8); // Start from 8 AM
            $dayEnd = $currentDate->copy()->endOfDay()->subHours(2); // End at 10 PM

            // Check hourly slots
            for ($hour = 0; $hour < 14; $hour++) { // 8 AM to 10 PM = 14 hours
                $slotStart = $dayStart->copy()->addHours($hour);
                $slotEnd = $slotStart->copy()->addMinutes($durationInMinutes);

                // Skip if the slot would extend beyond the day's end
                if ($slotEnd > $dayEnd) {
                    continue;
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
     * Get daily schedule optimization suggestions
     */
    public function optimizeDailySchedule(Carbon $startDate, Carbon $endDate): array
    {
        $activities = Activity::where('start_date', $startDate)
            ->where('end_date', $endDate)
            ->orderBy('priority', 'desc')
            ->orderBy('start_date', 'asc')
            ->get();

        $suggestions = [];
        $conflicts = [];

        foreach ($activities as $activity) {
            if (!$activity->end_date) {
                continue;
            }

            $activityConflicts = Activity::findConflicts(
                $activity->start_date,
                $activity->end_date,
                $activity->id
            )->where('start_date', '>=', $startDate)
                ->where('end_date', '<=', $endDate)
                ->get();

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
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_activities' => $activities->count(),
            'conflicts' => $conflicts,
            'suggestions' => $suggestions
        ];
    }
}
