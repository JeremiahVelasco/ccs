<?php

namespace App\Filament\Widgets;

use App\Models\Group;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class FacultyAdvisedGroupsWidget extends BaseWidget
{
    protected static ?string $heading = 'Groups Under My Guidance';

    public function table(Table $table): Table
    {
        $user = Auth::user();

        return $table
            ->query(
                Group::query()
                    ->where('adviser', $user->id)
                    ->where('status', 'Active')
                    ->with(['project', 'leader', 'members'])
            )
            ->recordUrl(
                fn(Group $record): string =>
                $record->project ? route('filament.admin.resources.projects.view', ['record' => $record->id]) : '#'
            )
            ->columns([
                TextColumn::make('name')
                    ->weight('bold'),

                TextColumn::make('project.status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Done' => 'success',
                        'For Review' => 'warning',
                        'In Progress' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('project.progress')
                    ->label('Progress')
                    ->suffix('%')
                    ->color(fn($state) => $state >= 75 ? 'success' : ($state >= 50 ? 'warning' : 'danger')),

                TextColumn::make('project.completion_probability')
                    ->label('Completion Probability')
                    ->formatStateUsing(fn($state) => round($state * 100) . '%')
                    ->color(fn($state) => $state >= 75 ? 'success' : ($state >= 50 ? 'warning' : 'danger')),
            ])
            ->actions([
                //
            ])
            ->paginated(false)
            ->emptyStateHeading('No groups assigned')
            ->emptyStateDescription('You are not currently advising any groups.')
            ->emptyStateIcon('heroicon-o-user-group');
    }
}
