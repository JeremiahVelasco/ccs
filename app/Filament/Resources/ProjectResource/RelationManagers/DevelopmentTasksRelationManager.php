<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Facades\Filament;
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

class DevelopmentTasksRelationManager extends RelationManager
{
    protected static string $relationship = 'developmentTasks';

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
            ->columns([
                TextColumn::make('title'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'Done' => 'success',
                        'For Review' => 'warning',
                        'To-do' => 'danger',
                        'In Progress' => 'info',
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Action::make('Approve')
                    ->icon('heroicon-m-check-badge')
                    ->visible(fn(Model $task) => $task->status !== 'Approved' && $task->status !== 'To-do' && $task->status !== 'In Progress')
                    ->requiresConfirmation()
                    ->modalDescription('Are you sure you\'d like to Approve this task?')
                    ->action(fn(Model $task) => $task->markAsDone()),
                Action::make('Disapprove')
                    ->visible(fn(Model $task) => $task->status === 'Approved')
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
