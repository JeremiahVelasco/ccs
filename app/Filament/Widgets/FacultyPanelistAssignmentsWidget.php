<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class FacultyPanelistAssignmentsWidget extends BaseWidget
{
    protected static ?string $heading = 'My Panelist Assignments';

    public function table(Table $table): Table
    {
        $user = Auth::user();

        return $table
            ->query(
                Project::query()
                    ->where('status', 'For Review')
                    ->whereJsonContains('panelists', (string) $user->id)
                    ->with(['group.leader', 'group.adviser'])
            )
            ->columns([
                ImageColumn::make('logo')
                    ->circular()
                    ->size(40),

                TextColumn::make('title')
                    ->weight('bold')
                    ->limit(50),

                TextColumn::make('group.name')
                    ->label('Group'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Done' => 'success',
                        'For Review' => 'warning',
                        'In Progress' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('progress')
                    ->suffix('%')
                    ->color(fn($state) => $state >= 75 ? 'success' : ($state >= 50 ? 'warning' : 'danger')),

                TextColumn::make('graded_status')
                    ->label('Graded')
                    ->getStateUsing(function ($record) use ($user) {
                        $hasGraded = $record->grades()->where('panel_id', $user->id)->exists();
                        return $hasGraded ? 'Yes' : 'No';
                    })
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Yes' => 'success',
                        'No' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('grade')
                    ->icon('heroicon-m-star')
                    ->label('Grade')
                    ->url(
                        fn(Project $record): string =>
                        route('filament.admin.resources.groups.edit', ['record' => $record->group->id]) . '?tab=grading'
                    )
                    ->openUrlInNewTab()
                    ->visible(function (Project $record) use ($user) {
                        return !$record->grades()->where('panel_id', $user->id)->exists();
                    }),

                Tables\Actions\Action::make('view')
                    ->icon('heroicon-m-eye')
                    ->url(
                        fn(Project $record): string =>
                        route('filament.admin.resources.groups.edit', ['record' => $record->group->id])
                    )
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('No panelist assignments')
            ->emptyStateDescription('You are not currently assigned as a panelist for any projects.')
            ->emptyStateIcon('heroicon-o-clipboard-document-check');
    }
}
