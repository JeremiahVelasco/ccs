<?php

namespace App\Filament\Widgets;

use App\Models\Task;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class StudentProgressWidget extends ChartWidget
{
    protected static ?string $heading = 'My Progress Overview';

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $user = Auth::user();
        $project = $user->group?->project;

        if (!$project) {
            return [
                'datasets' => [
                    [
                        'label' => 'Tasks',
                        'data' => [0, 0, 0, 0],
                        'backgroundColor' => [
                            '#ef4444',
                            '#f59e0b',
                            '#3b82f6',
                            '#10b981',
                        ],
                    ],
                ],
                'labels' => ['To-do', 'In Progress', 'For Review', 'Approved'],
            ];
        }

        $tasks = Task::where('project_id', $project->id)
            ->where(function ($query) use ($user) {
                $query->whereJsonContains('assigned_to', $user->id)
                    ->orWhereNull('assigned_to');
            })
            ->get();

        $statusCounts = [
            'To-do' => $tasks->where('status', 'To-do')->count(),
            'In Progress' => $tasks->where('status', 'In Progress')->count(),
            'For Review' => $tasks->where('status', 'For Review')->count(),
            'Approved' => $tasks->where('status', 'Approved')->count(),
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Tasks',
                    'data' => array_values($statusCounts),
                    'backgroundColor' => [
                        '#ef4444',
                        '#f59e0b',
                        '#3b82f6',
                        '#10b981',
                    ],
                ],
            ],
            'labels' => array_keys($statusCounts),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
