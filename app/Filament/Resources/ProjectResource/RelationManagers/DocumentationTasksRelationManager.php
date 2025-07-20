<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Filament\Clusters\Tasks\Resources\TaskResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DocumentationTasksRelationManager extends RelationManager
{
    protected static string $relationship = 'documentationTasks';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->recordUrl(fn(Model $record) => TaskResource::getUrl('edit', ['record' => $record->id]))
            ->columns([
                TextColumn::make('title'),
                TextColumn::make('file_path')
                    ->label('Has file')
                    ->formatStateUsing(fn($state) => $state ? 'Yes' : 'No')
                    ->color(fn($state) => $state ? 'success' : 'danger'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'Approved' => 'success',
                        'For Review' => 'warning',
                        'To-do' => 'danger',
                        'In Progress' => 'info',
                    }),
            ])
            ->paginated(false)
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Action::make('Approve')
                    ->icon('heroicon-m-check-badge')
                    ->visible(fn(Model $task) => $task->status !== 'Approved' && $task->status !== 'To-do' && $task->status !== 'In Progress' && !auth()->user()->isStudent())
                    ->requiresConfirmation()
                    ->modalDescription('Are you sure you\'d like to Approve this task?')
                    ->action(fn(Model $task) => $task->markAsDone($task->id)),
                Action::make('Disapprove')
                    ->visible(fn(Model $task) => $task->status === 'Approved' && !auth()->user()->isStudent())
                    ->icon('heroicon-m-backward')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('Are you sure you\'d like to revert this task to "For Review"?')
                    ->action(fn(Model $task) => $task->revertApproval())
            ])
            ->bulkActions([
                //
            ]);
    }
}
