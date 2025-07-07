<?php

namespace App\Filament\Clusters\Tasks\Resources;

use App\Filament\Clusters\Tasks;
use App\Filament\Clusters\Tasks\Resources\TaskResource\Pages;
use App\Filament\Clusters\Tasks\Resources\TaskResource\RelationManagers;
use App\Models\Task;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Tasks::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->required()
                    ->disabled()
                    ->maxLength(255),
                DatePicker::make('deadline'),
                Select::make('assigned_to')
                    ->options(User::query()->where('group_id', Auth::user()->group_id)->pluck('name', 'id')->toArray()),
                Select::make('status')
                    ->options([
                        'To-do' => 'To-do',
                        'In Progress' => 'In Progress',
                        'For Review' => 'For Review',
                        'Approved' => 'Approved',
                    ])
                    ->required(),
                Select::make('is_faculty_approved')
                    ->options([
                        1 => 'Yes',
                        0 => 'No',
                    ])
                    ->required(),
                DatePicker::make('date_accomplished'),
                Textarea::make('description')
                    ->columnSpanFull()
                    ->rows(3)
                    ->maxLength(255),
                FileUpload::make('file_path')
                    ->columnSpanFull()
                    ->disk('public')
                    ->directory('task-files')
                    ->previewable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
            'view' => Pages\ViewTask::route('/{record}'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
