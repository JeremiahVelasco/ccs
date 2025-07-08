<?php

namespace App\Filament\Widgets;

use App\Filament\Clusters\Tasks\Resources\TaskResource;
use App\Models\Task;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class StudentTasksWidget extends BaseWidget
{
    protected static ?string $heading = 'Tasks';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $project = $user->group?->project;

        return $table
            ->query(
                Task::query()
                    ->when($project, fn($query) => $query->where('project_id', $project->id))
                    ->where(function ($query) use ($user) {
                        $query->whereJsonContains('assigned_to', $user->id)
                            ->orWhereNull('assigned_to');
                    })
                    ->whereIn('status', ['To-do', 'In Progress', 'For Review'])
                    ->orderBy('deadline')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->limit(50),

                TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'documentation' => 'info',
                        'development' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Approved' => 'success',
                        'For Review' => 'warning',
                        'In Progress' => 'info',
                        'To-do' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('deadline')
                    ->date()
                    ->sortable()
                    ->color(
                        fn($record) =>
                        $record->deadline && $record->deadline->isPast() ? 'danger' : null
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->icon('heroicon-m-eye')
                    ->url(
                        fn(Task $record): string =>
                        TaskResource::getUrl('edit', ['record' => $record->id]),
                    ),
            ])
            ->emptyStateHeading('No tasks assigned')
            ->emptyStateDescription('You don\'t have any pending tasks at the moment.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
