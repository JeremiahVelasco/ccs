<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ActivityResource;
use App\Models\Activity;
use App\Services\PrioritySchedulerService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Saade\FilamentFullCalendar\Actions;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarWidget extends FullCalendarWidget
{
    protected function getScheduler(): PrioritySchedulerService
    {
        return app(PrioritySchedulerService::class);
    }

    public function fetchEvents(array $fetchInfo): array
    {
        return Activity::query()
            ->where('start_date', '<=', $fetchInfo['end'])
            ->where('end_date', '>=', $fetchInfo['start'])
            ->get()
            ->map(
                fn(Activity $activity) => [
                    'id' => $activity->id,
                    'title' => $activity->title,
                    'start' => $activity->start_date,
                    'end' => $activity->end_date,
                    'url' => ActivityResource::getUrl(name: 'view', parameters: ['record' => $activity]),
                    'shouldOpenUrlInNewTab' => false,
                    'backgroundColor' => $this->getPriorityColor($activity->priority),
                    'borderColor' => $this->getPriorityColor($activity->priority),
                    'classNames' => ['priority-' . $activity->priority],
                    'extendedProps' => [
                        'priority' => $activity->priority,
                        'priority_label' => $activity->priority_label,
                        'description' => $activity->description,
                        'is_flexible' => $activity->is_flexible,
                    ]
                ]
            )
            ->all();
    }

    protected function headerActions(): array
    {
        // Students cannot create events
        if (Auth::user()?->isStudent()) {
            return [];
        }

        return [
            Actions\CreateAction::make()
                ->mountUsing(
                    function (Form $form, array $arguments) {
                        // Handle different argument structures that FullCalendar might pass
                        $startDate = $arguments['start'] ?? $arguments['start_date'] ?? $arguments['startStr'] ?? now();
                        $endDate = $arguments['end'] ?? $arguments['end_date'] ?? $arguments['endStr'] ?? now()->addHours(1);

                        // If the dates are strings, parse them
                        if (is_string($startDate)) {
                            $startDate = \Carbon\Carbon::parse($startDate);
                        }
                        if (is_string($endDate)) {
                            $endDate = \Carbon\Carbon::parse($endDate);
                        }

                        $form->fill([
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                            'priority' => Activity::PRIORITY_MEDIUM,
                            'is_flexible' => true,
                        ]);
                    }
                )
                ->action(function (array $data) {
                    $result = $this->getScheduler()->scheduleActivity($data);

                    if ($result['success']) {
                        Notification::make()
                            ->title('Activity Scheduled')
                            ->body($result['message'])
                            ->success()
                            ->send();

                        if (!empty($result['rescheduled'])) {
                            $rescheduledTitles = collect($result['rescheduled'])
                                ->pluck('activity.title')
                                ->join(', ');

                            Notification::make()
                                ->title('Activities Rescheduled')
                                ->body("The following activities were automatically rescheduled: {$rescheduledTitles}")
                                ->warning()
                                ->send();
                        }
                    } else {
                        Notification::make()
                            ->title('Scheduling Conflict')
                            ->body($result['message'])
                            ->warning()
                            ->send();

                        // Show debug information
                        if (!empty($result['debug_info'])) {
                            $debugInfo = $result['debug_info'];

                            $debugBody = "DEBUG INFO:\n\n";
                            $debugBody .= "New Activity: " . $debugInfo['new_activity']['title'] . " (Priority: " . $debugInfo['new_activity']['priority_label'] . ")\n";
                            $debugBody .= "Time: " . $debugInfo['new_activity']['start_date'] . " to " . $debugInfo['new_activity']['end_date'] . "\n\n";

                            if (!empty($debugInfo['conflicts_found']['details'])) {
                                $debugBody .= "Conflicts Found (" . $debugInfo['conflicts_found']['count'] . "):\n";
                                foreach ($debugInfo['conflicts_found']['details'] as $conflict) {
                                    $debugBody .= "- " . $conflict['title'] . " (Priority: " . $conflict['priority_label'] . ", Flexible: " . ($conflict['is_flexible'] ? 'Yes' : 'No') . ")\n";
                                    $debugBody .= "  Time: " . $conflict['start_date'] . " to " . $conflict['end_date'] . "\n";
                                }
                                $debugBody .= "\n";
                            }

                            if (!empty($debugInfo['priority_analysis'])) {
                                $analysis = $debugInfo['priority_analysis'];
                                $debugBody .= "Priority Analysis:\n";
                                $debugBody .= "- Higher priority conflicts: " . $analysis['higher_priority_conflicts']['count'] . "\n";
                                $debugBody .= "- Same priority conflicts: " . $analysis['same_priority_conflicts']['count'] . "\n";
                                $debugBody .= "- Lower priority conflicts: " . $analysis['lower_priority_conflicts']['count'] . "\n\n";
                            }

                            if (!empty($debugInfo['decision_path'])) {
                                $debugBody .= "Decision Path:\n";
                                foreach ($debugInfo['decision_path'] as $step) {
                                    $debugBody .= "- " . $step . "\n";
                                }
                            }

                            Notification::make()
                                ->title('Debug Information')
                                ->body($debugBody)
                                ->info()
                                ->send();
                        }

                        // You could also show suggested times in a modal or additional notification
                        if (!empty($result['suggested_times'])) {
                            $suggestions = collect($result['suggested_times'])
                                ->map(fn($slot) => $slot['day'] . ' at ' . $slot['time'])
                                ->take(3)
                                ->join(', ');

                            Notification::make()
                                ->title('Suggested Times')
                                ->body("Available slots: {$suggestions}")
                                ->info()
                                ->send();
                        }
                    }
                })
        ];
    }

    protected function modalActions(): array
    {
        // Students cannot edit or delete events
        if (Auth::user()?->isStudent()) {
            return [];
        }

        return [
            Actions\EditAction::make()
                ->mountUsing(
                    function (Form $form, array $arguments) {
                        // Find the activity by ID from the event
                        $activity = Activity::find($arguments['id'] ?? null);

                        if ($activity) {
                            $form->fill([
                                'title' => $activity->title,
                                'description' => $activity->description,
                                'start_date' => $activity->start_date,
                                'end_date' => $activity->end_date,
                                'priority' => $activity->priority,
                                'is_flexible' => $activity->is_flexible,
                                'category' => $activity->category,
                            ]);
                        } else {
                            $form->fill($arguments);
                        }
                    }
                )
                ->action(function (array $data, array $arguments) {
                    $activity = Activity::find($arguments['id'] ?? null);

                    if ($activity) {
                        // Check if the time or priority changed
                        $dateChanged = $activity->start_date->ne($data['start_date']);
                        $priorityChanged = $activity->priority !== $data['priority'];

                        if ($dateChanged || $priorityChanged) {
                            // Use scheduler to handle potential conflicts
                            $result = $this->getScheduler()->scheduleActivity(array_merge($data, [
                                'id' => $activity->id // Exclude from conflict check
                            ]));

                            if ($result['success']) {
                                $activity->update($data);

                                Notification::make()
                                    ->title('Activity Updated')
                                    ->body($result['message'])
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Update Conflict')
                                    ->body($result['message'])
                                    ->warning()
                                    ->send();

                                return; // Don't update if there are conflicts
                            }
                        } else {
                            // No scheduling conflicts, just update
                            $activity->update($data);

                            Notification::make()
                                ->title('Activity Updated')
                                ->success()
                                ->send();
                        }
                    }
                }),
            Actions\DeleteAction::make()
                ->action(function (array $arguments) {
                    $activity = Activity::find($arguments['id'] ?? null);

                    if ($activity) {
                        $activity->delete();

                        Notification::make()
                            ->title('Activity Deleted')
                            ->success()
                            ->send();
                    }
                }),
        ];
    }

    public function getFormSchema(): array
    {
        return [
            Grid::make(2)
                ->schema([
                    TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(2),
                    DateTimePicker::make('start_date')
                        ->required()
                        ->default(now()),
                    DateTimePicker::make('end_date')
                        ->required()
                        ->default(now()->addHours(1)),
                    Select::make('priority')
                        ->options(Activity::getPriorityOptions())
                        ->default(Activity::PRIORITY_MEDIUM)
                        ->required(),
                    Select::make('category')
                        ->required()
                        ->options(Activity::getCategoryOptions()),
                    Toggle::make('is_flexible')
                        ->label('Can be rescheduled automatically')
                        ->default(true)
                        ->columnSpan(2),
                ]),
            Textarea::make('description')
                ->rows(3)
                ->maxLength(1000),
        ];
    }

    /**
     * Get color based on priority level
     */
    private function getPriorityColor(int $priority): string
    {
        return match ($priority) {
            Activity::PRIORITY_LOW => '#10B981',      // Green
            Activity::PRIORITY_MEDIUM => '#F59E0B',   // Amber
            Activity::PRIORITY_HIGH => '#EF4444',     // Red
            Activity::PRIORITY_URGENT => '#7C3AED',   // Purple
            default => '#6B7280',                     // Gray
        };
    }
}
