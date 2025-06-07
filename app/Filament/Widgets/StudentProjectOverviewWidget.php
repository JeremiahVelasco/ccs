<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class StudentProjectOverviewWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        $project = $user->group?->project;

        if (!$project) {
            return [
                Stat::make('Project Status', 'No Project')
                    ->description('You haven\'t been assigned to a project yet')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('warning'),
            ];
        }

        $totalTasks = $project->documentationTasks()->count() + $project->developmentTasks()->count();
        $completedTasks = $project->documentationTasks()->where('status', 'Approved')->count() +
            $project->developmentTasks()->where('status', 'Approved')->count();
        $progressPercentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        return [
            Stat::make('Project Status', $project->status)
                ->description($project->title)
                ->descriptionIcon('heroicon-m-folder')
                ->color($this->getStatusColor($project->status)),

            Stat::make('Overall Progress', $progressPercentage . '%')
                ->description("$completedTasks of $totalTasks tasks completed")
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color($progressPercentage >= 75 ? 'success' : ($progressPercentage >= 50 ? 'warning' : 'danger')),

            Stat::make('Documentation Tasks', $project->documentationTasks()->where('status', 'Approved')->count())
                ->description('of ' . $project->documentationTasks()->count() . ' total')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

            Stat::make('Development Tasks', $project->developmentTasks()->where('status', 'Approved')->count())
                ->description('of ' . $project->developmentTasks()->count() . ' total')
                ->descriptionIcon('heroicon-m-code-bracket')
                ->color('primary'),
        ];
    }

    private function getStatusColor(string $status): string
    {
        return match ($status) {
            'Done' => 'success',
            'For Review' => 'warning',
            'In Progress' => 'info',
            default => 'gray'
        };
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
