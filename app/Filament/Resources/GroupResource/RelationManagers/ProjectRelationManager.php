<?php

namespace App\Filament\Resources\GroupResource\RelationManagers;

use App\Filament\Resources\ProjectResource;
use App\Models\Project;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Attributes\Layout;

class ProjectRelationManager extends RelationManager
{
    protected static string $relationship = 'project';

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
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->recordTitleAttribute('title')
            ->recordUrl(fn(Project $record) => ProjectResource::getUrl('edit', ['record' => $record]))
            ->columns([
                Stack::make([
                    ImageColumn::make('logo'),
                    TextColumn::make('title'),
                    TextColumn::make('status')
                        ->badge()
                        ->color(fn($state) => match ($state) {
                            'In Progress' => 'primary',
                            'For Review' => 'warning',
                            'Done' => 'success',
                        }),
                    TextColumn::make('deadline')
                        ->dateTimeTooltip()
                        ->date()
                        ->description(fn($state) => $state ? Carbon::parse($state)->diffForHumans() : null)
                        ->color(fn($state) => $state && Carbon::parse($state)->isPast() ? 'danger' : 'success'),
                    Split::make([
                        TextColumn::make('progress')
                            ->numeric(2)
                            ->suffix('%'),
                        TextColumn::make('final_grade')
                            ->numeric()
                            ->suffix('%'),
                    ]),
                ]),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }
}
