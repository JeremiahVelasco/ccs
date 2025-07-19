<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use App\Models\Group;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class FacultyProjectsOverviewWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();

        // Projects where faculty is an adviser
        $advisedProjects = Project::whereHas('group', function ($query) use ($user) {
            $query->where('adviser', $user->id);
        })->count();

        // Projects where faculty is a panelist
        $panelistProjects = Project::whereJsonContains('panelists', $user->id)->count();

        // Active Groups
        $activeGroups = Group::where('status', 'Active')->count();

        // Active projects in system (for context)
        $activeProjects = Project::whereIn('status', ['In Progress', 'For Review'])->count();

        return [
            Stat::make('Advised Projects', $advisedProjects)
                ->description('Groups under your guidance')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make('Panelist Assignments', $panelistProjects)
                ->description('Projects you\'re evaluating')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('info'),

            Stat::make('Active Groups', $activeGroups)
                ->description('Groups under your guidance')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),


            Stat::make('Active Projects', $activeProjects)
                ->description('All active projects in system')
                ->descriptionIcon('heroicon-m-folder')
                ->color('gray'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
