<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Group;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('logo')
                    ->hiddenLabel()
                    ->directory('project-logos')
                    ->avatar()
                    ->columnSpanFull()
                    ->alignCenter(),
                TextInput::make('title'),
                Select::make('group_id')
                    ->label('Group')
                    ->options(Group::all()->pluck('name', 'id')),
                Textarea::make('description')
                    ->helperText('Maximum: 30 characters')
                    ->maxLength(30)
                    ->rows(2)
                    ->columnSpanFull(),
                Select::make('status')
                    ->default('In Progress')
                    ->options([
                        'In Progress' => 'In Progress',
                        'For Review' => 'For Review',
                        'Done' => 'Done'
                    ]),
                TextInput::make('progress')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%'),
                TextInput::make('final_grade')
                    ->numeric(),
                TagsInput::make('awards')
                    ->suggestions([
                        '🏆 Best Capstone',
                        '💡 Most Innovative',
                        '🖥️ Best Web App',
                        '📱 Best Mobile App'
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('progress')
                    ->badge()
                    ->suffix('%'),
                TextColumn::make('title'),
                TextColumn::make('group.name'),
                TextColumn::make('description')
                    ->limit(30),
                TextColumn::make('awards'),
                TextColumn::make('final_grade'),
                TextColumn::make('status')
                    ->badge(),
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
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()->specialAccess();
    }
}
