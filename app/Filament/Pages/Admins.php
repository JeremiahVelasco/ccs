<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class Admins extends Page implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static string $view = 'filament.pages.admins';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->user()->specialAccess();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(User::admins())
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make()
                    ->form([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('student_id')
                            ->label('Student ID'),
                        TextInput::make('email')
                            ->required(),
                        TextInput::make('course')
                            ->required(),
                        TextInput::make('group_id'),
                        TextInput::make('group_role')
                    ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
