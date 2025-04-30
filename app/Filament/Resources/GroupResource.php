<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GroupResource\Pages;
use App\Filament\Resources\GroupResource\RelationManagers;
use App\Filament\Resources\GroupResource\RelationManagers\MembersRelationManager;
use App\Models\Group;
use App\Models\User;
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

class GroupResource extends Resource
{
    protected static ?string $model = Group::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('logo')
                    ->directory('group-logos')
                    ->avatar()
                    ->columnSpanFull()
                    ->alignCenter(),
                TextInput::make('name')
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->columnSpanFull(),
                Select::make('status')
                    ->searchable()
                    ->options([
                        'Active' => 'Active',
                        'Inactive' => 'Inactive',
                    ]),
                Select::make('leader')
                    ->searchable()
                    ->options(function () {
                        return User::role('student')->pluck('name', 'id');
                    }),
                Select::make('adviser')
                    ->searchable()
                    ->options(function () {
                        return User::role('faculty')->pluck('name', 'id');
                    }),
                TagsInput::make('panelists')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('adviser')
                    ->label('Adviser'),
                TextColumn::make('panelists'),
                TextColumn::make('group_code'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Active' => 'success',
                        'Inactive' => 'gray',
                        default => 'green',
                    })
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
            MembersRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGroups::route('/'),
            'create' => Pages\CreateGroup::route('/create'),
            'edit' => Pages\EditGroup::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()->specialAccess();
    }
}
