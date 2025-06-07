<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class FacultyGradingPendingWidget extends Widget
{
    protected static string $view = 'filament.widgets.faculty-grading-pending';

    protected int | string | array $columnSpan = 1;

    public function getViewData(): array
    {
        $user = Auth::user();

        $pendingProjects = Project::whereJsonContains('panelists', $user->id)
            ->whereDoesntHave('grades', function ($query) use ($user) {
                $query->where('panel_id', $user->id);
            })
            ->with(['group.leader', 'group.adviser'])
            ->limit(5)
            ->get();

        return [
            'pendingProjects' => $pendingProjects,
            'totalPending' => $pendingProjects->count(),
        ];
    }
}
