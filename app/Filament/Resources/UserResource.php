<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('avatar')
                    ->hiddenLabel()
                    ->alignCenter()
                    ->columnSpanFull()
                    ->label('Avatar')
                    ->image()
                    ->avatar(),
                TextInput::make('name'),
                Select::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->live(),
                TextInput::make('email')
                    ->label('Email')
                    ->required(),
                TextInput::make('student_id')
                    ->label('Student ID')
                    ->visible(function ($get) {
                        $roleIds = $get('roles');
                        if (!$roleIds) return false;

                        // Handle both single ID and array of IDs
                        $roleIds = is_array($roleIds) ? $roleIds : [$roleIds];

                        $roles = \App\Models\Role::whereIn('id', $roleIds)->get();
                        return $roles->contains('name', 'student');
                    }),
                Select::make('course')
                    ->options([
                        'BSITWMA' => 'BSITWMA',
                        'BSITAGD' => 'BSITAGD',
                    ])
                    ->visible(function ($get) {
                        $roleIds = $get('roles');
                        if (!$roleIds) return false;

                        // Handle both single ID and array of IDs
                        $roleIds = is_array($roleIds) ? $roleIds : [$roleIds];

                        $roles = \App\Models\Role::whereIn('id', $roleIds)->get();
                        return $roles->contains('name', 'student');
                    }),
                Select::make('group_role')
                    ->label('Group Role')
                    ->options([
                        'project_manager' => 'Project Manager',
                        'developer' => 'Developer',
                        'designer' => 'Designer',
                        'tester' => 'Tester',
                        'other' => 'Other',
                    ])
                    ->visible(function ($get) {
                        $roleIds = $get('roles');
                        if (!$roleIds) return false;

                        // Handle both single ID and array of IDs
                        $roleIds = is_array($roleIds) ? $roleIds : [$roleIds];

                        $roles = \App\Models\Role::whereIn('id', $roleIds)->get();
                        return $roles->contains('name', 'student');
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('roles.name'),
                TextColumn::make('student_id')
                    ->label('Student ID')
                    ->visible(fn($record) => $record?->hasRole('student'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('course')
                    ->visible(fn($record) => $record?->hasRole('student'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('group_role')
                    ->label('Group Role')
                    ->visible(fn($record) => $record?->hasRole('student'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->native(false),
                SelectFilter::make('group_role')
                    ->label('Group Role')
                    ->options([
                        'project_manager' => 'Project Manager',
                        'developer' => 'Developer',
                        'designer' => 'Designer',
                        'tester' => 'Tester',
                        'other' => 'Other',
                    ])
                    ->native(false),
                SelectFilter::make('course')
                    ->label('Course')
                    ->options([
                        'BSITWMA' => 'BSITWMA',
                        'BSITAGD' => 'BSITAGD',
                    ])
                    ->native(false),
            ], layout: FiltersLayout::AboveContent)
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
