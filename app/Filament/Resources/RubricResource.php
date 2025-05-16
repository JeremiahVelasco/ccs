<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RubricResource\Pages;
use App\Filament\Resources\RubricResource\RelationManagers;
use App\Models\Rubric;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RubricResource extends Resource
{
    protected static ?string $model = Rubric::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Rubric Details')
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('max_score')
                            ->label('Maximum Total Score')
                            ->numeric(),
                        Textarea::make('description')
                            ->columnSpanFull()
                            ->nullable()
                    ]),
                Section::make('Rubric Criteria')
                    ->schema([
                        Repeater::make('criteria')
                            ->relationship()
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),

                                Textarea::make('description')
                                    ->maxLength(1000)
                                    ->rows(2),

                                Grid::make()
                                    ->schema([
                                        TextInput::make('weight')
                                            ->label('Weight (%)')
                                            ->numeric()
                                            ->required()
                                            ->minValue(1)
                                            ->maxValue(100)
                                            ->default(10)
                                            ->helperText('Percentage weight in final score'),

                                        TextInput::make('max_score')
                                            ->label('Maximum Score')
                                            ->numeric()
                                            ->required()
                                            ->minValue(1)
                                            ->default(10),
                                    ]),
                            ])
                            ->itemLabel(fn(array $state): ?string => $state['name'] ?? null)
                            ->collapsible()
                            ->defaultItems(0)
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('criteria_count')
                    ->label('Criteria')
                    ->getStateUsing(fn(Rubric $record): int => $record->criteria()->count())
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->withCount('criteria')
                            ->orderBy('criteria_count', $direction);
                    }),

                TextColumn::make('max_score')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRubrics::route('/'),
            'create' => Pages\CreateRubric::route('/create'),
            'edit' => Pages\EditRubric::route('/{record}/edit'),
            'view' => Pages\ViewRubric::route('/{record}'),
        ];
    }
}
