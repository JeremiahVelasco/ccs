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
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Hidden::make('project_id')
                    ->default(function () {
                        $user = Auth::user();
                        return Project::where('group_id', $user->group_id)->first()?->id;
                    }),
                Textarea::make('description')
                    ->maxLength(1000)
                    ->rows(3)
                    ->columnSpanFull(),
                TextInput::make('file_link')
                    ->label('External Link (Optional)')
                    ->url()
                    ->helperText('Add a link to an external file (Google Drive, Dropbox, etc.)')
                    ->columnSpanFull(),
                FileUpload::make('file_path')
                    ->label('Upload File')
                    ->disk('public')
                    ->directory('project-files')
                    ->visibility('public')
                    ->preserveFilenames()
                    ->previewable()
                    ->downloadable()
                    ->openable()
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-powerpoint',
                        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                        'text/plain',
                        'text/csv',
                        'image/jpeg',
                        'image/png',
                        'image/gif',
                        'application/zip',
                        'application/x-rar-compressed'
                    ])
                    ->maxSize(10240) // 10MB
                    ->helperText('Supported formats: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, CSV, JPG, PNG, GIF, ZIP, RAR (Max: 10MB)')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function (Builder $query) {
                if (Auth::user()->isStudent()) {
                    $user = Auth::user();
                    if ($user->group && $user->group->project) {
                        return $query->where('project_id', $user->group->project->id);
                    }
                    return $query->whereNull('project_id'); // Return empty query if no project
                }

                return $query;
            })
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('project.title')
                    ->hidden(fn() => Auth::user()->isStudent())
                    ->label('Project')
                    ->searchable(),
                TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('file_path')
                    ->label('File')
                    ->state(function (Model $record) {
                        if ($record->file_path) {
                            return basename($record->file_path);
                        }
                        return 'No file uploaded';
                    })
                    ->badge()
                    ->color(fn(Model $record) => $record->file_path ? 'success' : 'gray'),
                TextColumn::make('file_link')
                    ->label('External Link')
                    ->state(function (Model $record) {
                        return $record->file_link ? 'Yes' : 'No';
                    })
                    ->badge()
                    ->color(fn(Model $record) => $record->file_link ? 'info' : 'gray'),
                TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->options(Project::all()->pluck('title', 'id'))
                    ->hidden(fn() => Auth::user()->isStudent()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->tooltip('View File Details'),
                Tables\Actions\EditAction::make()
                    ->tooltip('Edit File'),
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('success')
                    ->url(fn(Model $record) => $record->file_path ? \Illuminate\Support\Facades\Storage::url($record->file_path) : null)
                    ->openUrlInNewTab()
                    ->visible(fn(Model $record) => $record->file_path)
                    ->tooltip('Download File'),
                Tables\Actions\Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-m-eye')
                    ->color('info')
                    ->url(fn(Model $record) => $record->file_path ? \Illuminate\Support\Facades\Storage::url($record->file_path) : null)
                    ->openUrlInNewTab()
                    ->visible(fn(Model $record) => $record->file_path && in_array(pathinfo($record->file_path, PATHINFO_EXTENSION), ['pdf', 'jpg', 'jpeg', 'png', 'gif']))
                    ->tooltip('Preview File'),
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
            'view' => Pages\ViewFile::route('/{record}'),
            'edit' => Pages\EditFile::route('/{record}/edit'),
        ];
    }
}
