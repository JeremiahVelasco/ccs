<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FileResource\Pages;
use App\Filament\Resources\FileResource\RelationManagers;
use App\Models\File;
use App\Models\Project;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class FileResource extends Resource
{
    protected static ?string $model = File::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->columnSpanFull(),
                Hidden::make('project_id') // Use Hidden instead of TextInput
                    ->default(function () {
                        $user = Auth::user();
                        // Make sure this actually returns a value
                        return Project::where('group_id', $user->group_id)->first()?->id;
                    }),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('file_link')
                    ->columnSpanFull(),
                FileUpload::make('file_path')
                    ->label('Upload file')
                    ->disk('public')
                    ->directory('project-files')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function (Builder $query) {
                if (Auth::user()->isStudent()) {
                    $user = Auth::user();
                    $query = Task::where('project_id', $user->group->project->id);

                    return $query;
                }

                return Task::all();
            })
            ->recordUrl(
                fn(Model $file): string => FileResource::getUrl('edit', ['record' => $file->id]),
            )
            ->columns([
                TextColumn::make('title'),
                TextColumn::make('project.title')
                    ->hidden(fn() => Auth::user()->isStudent())
                    ->label('Project'),
                TextColumn::make('description')
                    ->limit(30),
                TextColumn::make('file_link'),
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
            'index' => Pages\ListFiles::route('/'),
            'create' => Pages\CreateFile::route('/create'),
            'edit' => Pages\EditFile::route('/{record}/edit'),
        ];
    }
}
