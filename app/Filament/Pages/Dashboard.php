<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.dashboard';

    protected static bool $shouldRegisterNavigation = false;

    public function mount(): void
    {
        $user = Auth::user();

        if ($user->hasRole('student')) {
            redirect()->route('filament.admin.pages.student-dashboard');
        } elseif ($user->hasRole('faculty')) {
            redirect()->route('filament.admin.pages.faculty-dashboard');
        }
    }
}
