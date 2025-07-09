<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class FacultyAnalyticsWidget extends ChartWidget
{
    protected static ?string $heading = 'Project Status Distribution';

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $user = Auth::user();

        // Get all projects where faculty is involved (adviser or panelist)
        $advisedProjects = Project::whereHas('group', function ($query) use ($user) {
            $query->where('adviser', $user->id);
        })->get();

        $panelistProjects = Project::whereJsonContains('panelists', $user->id)->get();

        // Combine and remove duplicates
        $allProjects = $advisedProjects->merge($panelistProjects)->unique('id');

        $statusCounts = [
            'To-do' => $allProjects->where('status', 'To-do')->count(),
            'In Progress' => $allProjects->where('status', 'In Progress')->count(),
            'For Review' => $allProjects->where('status', 'For Review')->count(),
            'Done' => $allProjects->where('status', 'Done')->count(),
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Projects',
                    'data' => array_values($statusCounts),
                    'backgroundColor' => [
                        '#3b82f6',
                        '#f59e0b',
                        '#10b981',
                    ],
                ],
            ],
            'labels' => array_keys($statusCounts),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
