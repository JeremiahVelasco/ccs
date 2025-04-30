<?php

namespace App\Filament\Pages;

use App\Filament\Resources\UserResource;
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
use Illuminate\Database\Eloquent\Model;

class Students extends Page implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static string $view = 'filament.pages.students';

    protected static ?string $navigationGroup = 'User Management';

    public static function canAccess(): bool
    {
        return auth()->user()->specialAccess();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(User::students())
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('student_id')
                    ->label('Student ID')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('course')
                    ->searchable(),
                TextColumn::make('group.name')
                    ->searchable(),
                TextColumn::make('group_role')
            ])
            ->filters([
                SelectFilter::make('group_role')
                    ->options([
                        'leader' => 'Leader',
                        'member' => 'Member',
                    ])
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
