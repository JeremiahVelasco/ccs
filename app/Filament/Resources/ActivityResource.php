<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages;
use App\Filament\Resources\ActivityResource\RelationManagers;
use App\Models\Activity;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
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
                TextInput::make('title'),
                Select::make('priority')
                    ->options(Activity::getPriorityOptions())
                    ->label('Priority')
                    ->default(Activity::PRIORITY_MEDIUM)
                    ->required(),
                Select::make('category')
                    ->options(Activity::getCategoryOptions())
                    ->label('Category')
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
                DateTimePicker::make('start_date')
                    ->seconds(false)
                    ->label('Start'),
                DateTimePicker::make('end_date')
                    ->seconds(false)
                    ->label('End'),
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
                    ->description(
                        fn(Model $record): string =>
                        Carbon::parse($record->start_date)->diffForHumans()
                    ),
                TextColumn::make('end_date')
                    ->dateTime('M d Y - h:iA')
                    ->description(
                        fn(Model $record): string =>
                        Carbon::parse($record->end_date)->diffForHumans()
                    ),
                TextColumn::make('title'),
                TextColumn::make('description')
                    ->limit(20),
            ])
            ->filters([
                //
            ])
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
