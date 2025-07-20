<?php

namespace App\Filament\Resources\GroupResource\RelationManagers;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('n')
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->striped()
            ->recordUrl(
                fn(Model $user): string => UserResource::getUrl('edit', ['record' => $user->id]),
            )
            ->recordTitleAttribute('name')
            ->columns([
                ImageColumn::make('avatar')
                    ->circular(),
                TextColumn::make('name'),
                TextColumn::make('student_id')
                    ->label('Student ID'),
                TextColumn::make('email'),
                TextColumn::make('group_role')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'project_manager' => 'primary',
                        'developer' => 'success',
                        'designer' => 'warning',
                        'tester' => 'danger',
                        'member' => 'info',
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\Action::make('add_member')
                    ->label('Add Member')
                    ->form([
                        Select::make('member_id')
                            ->options(User::query()->role('student')->whereNull('group_id')->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Member')
                            ->placeholder('Select a member')
                            ->columnSpanFull(),
                        Select::make('group_role')
                            ->options([
                                'project_manager' => 'Project Manager',
                                'developer' => 'Developer',
                                'designer' => 'Designer',
                                'tester' => 'Tester',
                                'member' => 'Member',
                                'other' => 'Other',
                            ])
                            ->required()
                            ->label('Role')
                            ->placeholder('Select a role')
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data) {
                        if ($this->getOwnerRecord()->members->count() >= $this->getOwnerRecord()->max_group_size) {
                            Notification::make()
                                ->title('Maximum group size reached')
                                ->body('The group has reached the maximum number of members.')
                                ->danger()
                                ->send();
                            return;
                        }

                        $user = User::find($data['member_id']);
                        $user->group_id = $this->getOwnerRecord()->id;
                        $user->group_role = $data['group_role'];
                        $user->save();
                    })
                    ->modalWidth('sm'),
            ])
            ->actions([
                Tables\Actions\Action::make('remove_member')
                    ->requiresConfirmation()
                    ->label('Remove')
                    ->color('danger')
                    ->action(function (Model $user) {
                        $user->group_id = null;
                        $user->group_role = null;
                        $user->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
