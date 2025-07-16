<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages;
use App\Filament\Resources\ActivityResource\RelationManagers;
use App\Models\Activity;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->required(),
                TextInput::make('user_id')
                    ->label('Created by')
                    ->disabled()
                    ->required(fn($livewire) => !($livewire instanceof \Filament\Resources\Pages\CreateRecord))
                    ->hidden(fn($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                    ->formatStateUsing(fn($state) => $state ? \App\Models\User::find($state)?->name : null),
                Select::make('category')
                    ->options(Activity::getCategoryOptions())
                    ->label('Category')
                    ->required(),
                Select::make('priority')
                    ->options(Activity::getPriorityOptions())
                    ->label('Priority')
                    ->default(Activity::PRIORITY_MEDIUM)
                    ->required(),
                Select::make('is_flexible')
                    ->label('Can be rescheduled automatically')
                    ->default(true)
                    ->options([
                        true => 'Yes',
                        false => 'No',
                    ])
                    ->label('Flexible')
                    ->required(),
                Section::make('Date and Time')
                    ->schema([
                        DateTimePicker::make('start_date')
                            ->seconds(false)
                            ->label('Start'),
                        DateTimePicker::make('end_date')
                            ->seconds(false)
                            ->label('End'),
                    ])
                    ->columns(2),
                Textarea::make('description')
                    ->columnSpanFull()
                    ->rows(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('start_date')
                    ->dateTime('M d Y - h:iA')
                    ->sortable()
                    ->description(
                        fn(Model $record): string =>
                        Carbon::parse($record->start_date)->diffForHumans()
                    ),
                TextColumn::make('end_date')
                    ->dateTime('M d Y - h:iA')
                    ->sortable()
                    ->description(
                        fn(Model $record): string =>
                        Carbon::parse($record->end_date)->diffForHumans()
                    ),
                TextColumn::make('title')
                    ->description(
                        fn(Model $record): string =>
                        $record->user->name
                    ),
                TextColumn::make('description')
                    ->limit(20),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->options(User::faculty()->pluck('name', 'id'))
                    ->label('Created by'),
                SelectFilter::make('category')
                    ->options(Activity::getCategoryOptions())
                    ->label('Category'),
                SelectFilter::make('priority')
                    ->options(Activity::getPriorityOptions())
                    ->label('Priority'),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListActivities::route('/'),
            'create' => Pages\CreateActivity::route('/create'),
            'view' => Pages\ViewActivity::route('/{record}'),
            'edit' => Pages\EditActivity::route('/{record}/edit'),
        ];
    }
}
