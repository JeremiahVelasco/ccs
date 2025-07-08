<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class FacultyDashboard extends Page
{
    use HasFiltersForm;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static string $view = 'filament.pages.faculty-dashboard';

    protected static ?string $title = 'Faculty Dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = -2;

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole('faculty') ?? false;
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\FacultyProjectsOverviewWidget::class,
            \App\Filament\Widgets\FacultyAnalyticsWidget::class,
            // \App\Filament\Widgets\FacultyGradingPendingWidget::class,
            \App\Filament\Widgets\FacultyAdvisedGroupsWidget::class,
            \App\Filament\Widgets\FacultyPanelistAssignmentsWidget::class,
            \App\Filament\Widgets\CalendarWidget::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return [
            'md' => 2,
            'xl' => 3,
        ];
    }
}
