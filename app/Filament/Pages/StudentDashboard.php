<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class StudentDashboard extends Page
{
    use HasFiltersForm;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static string $view = 'filament.pages.student-dashboard';

    protected static ?string $title = 'Student Dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = -2;

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole('student') ?? false;
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\StudentProjectOverviewWidget::class,
            \App\Filament\Widgets\StudentProgressWidget::class,
            \App\Filament\Widgets\StudentRecentActivitiesWidget::class,
            \App\Filament\Widgets\StudentTasksWidget::class,
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
