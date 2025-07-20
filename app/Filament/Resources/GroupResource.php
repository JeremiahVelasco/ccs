<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GroupResource\Pages;
use App\Filament\Resources\GroupResource\RelationManagers;
use App\Filament\Resources\GroupResource\RelationManagers\MembersRelationManager;
use App\Filament\Resources\GroupResource\RelationManagers\ProjectRelationManager;
use App\Models\Group;
use App\Models\User;
use App\Services\GroupService;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

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
                    ->previewable()
                    ->image()
                    ->preserveFilenames()
                    ->disk('public')
                    ->avatar()
                    ->columnSpanFull()
                    ->alignCenter(),
                TextInput::make('name')
                    ->required()
                    ->columnSpanFull(),
                Select::make('status')
                    ->options([
                        'Active' => 'Active',
                        'Inactive' => 'Inactive',
                    ])
                    ->default('Active'),
                TextInput::make('group_code')
                    ->disabled(),
                Select::make('school_year')
                    ->label('School Year')
                    ->default((now()->year - 1) . '-' . now()->year)
                    ->options([
                        '2020-2021' => '2020-2021',
                        '2021-2022' => '2021-2022',
                        '2022-2023' => '2022-2023',
                        '2023-2024' => '2023-2024',
                        '2024-2025' => '2024-2025',
                        '2025-2026' => '2025-2026',
                        '2026-2027' => '2026-2027',
                        '2027-2028' => '2027-2028',
                        '2028-2029' => '2028-2029',
                        '2029-2030' => '2029-2030',
                        '2030-2031' => '2030-2031',
                        '2031-2032' => '2031-2032',
                        '2032-2033' => '2032-2033',
                        '2033-2034' => '2033-2034',
                        '2034-2035' => '2034-2035',
                        '2035-2036' => '2035-2036',
                        '2036-2037' => '2036-2037',
                        '2037-2038' => '2037-2038',
                    ])
                    ->required(),
                Select::make('course')
                    ->options([
                        'BSITAGD' => 'BSITAGD',
                        'BSITWMA' => 'BSITWMA',
                        'BSITDC' => 'BSITDC',
                    ]),
                TextInput::make('section')
                    ->label('Section'),
                Textarea::make('description')
                    ->columnSpanFull(),
                Select::make('leader_id')
                    ->label('Leader')
                    ->relationship('leader', 'name')
                    ->searchable()
                    ->optionsLimit(5)
                    ->placeholder('Set this later on')
                    ->disabled(fn(?Group $record) => !$record)
                    ->options(function (?Group $record) {
                        if (!$record) {
                            // When creating a new group, show all students without a group
                            return User::role('student')->whereNull('group_id')->pluck('name', 'id');
                        }
                        // When editing, show students in this group
                        return User::role('student')->where('group_id', $record->id)->pluck('name', 'id');
                    }),
                Select::make('adviser')
                    ->searchable()
                    ->helperText('Available Advisers: ' . User::role('faculty')
                        ->where(function ($query) {
                            $query->whereDoesntHave('advised')
                                ->orWhereHas('advised', function ($subQuery) {
                                    $subQuery->where('status', 'Active');
                                }, '<', 2);
                        })
                        ->pluck('name')
                        ->implode(', '))
                    ->options(function (?Group $record) {
                        $query = User::role('faculty');

                        if ($record && $record->adviser) {
                            // When editing, include the current adviser even if they have 2 or more groups
                            $query->where(function ($subQuery) use ($record) {
                                $subQuery->where('id', $record->adviser)
                                    ->orWhere(function ($innerQuery) {
                                        $innerQuery->whereDoesntHave('advised')
                                            ->orWhereHas('advised', function ($advisedQuery) {
                                                $advisedQuery->where('status', 'Active');
                                            }, '<', 2);
                                    });
                            });
                        } else {
                            // When creating, only show faculty with less than 2 active groups
                            $query->where(function ($subQuery) {
                                $subQuery->whereDoesntHave('advised')
                                    ->orWhereHas('advised', function ($advisedQuery) {
                                        $advisedQuery->where('status', 'Active');
                                    }, '<', 2);
                            });
                        }

                        return $query->pluck('name', 'id');
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('course'),
                TextColumn::make('section'),
                TextColumn::make('school_year'),
                TextColumn::make('group_code'),
                TextColumn::make('adviser')
                    ->label('Adviser')
                    ->formatStateUsing(fn($state) => User::find($state)?->name),
                TextColumn::make('members_count')
                    ->label('Members')
                    ->counts('members'),
                // ->suffix(fn($record) => ' / ' . GroupService::computeMaxGroupsAndMembers($record->course)),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn($record) => $record->status === 'Active' ? 'success' : 'danger'),
            ])
            ->filters([
                SelectFilter::make('course')
                    ->options([
                        'BSITAGD' => 'BSITAGD',
                        'BSITWMA' => 'BSITWMA',
                        'BSITDC' => 'BSITDC',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'Active' => 'Active',
                        'Inactive' => 'Inactive',
                    ]),
                SelectFilter::make('school_year')
                    ->label('School Year')
                    ->default((now()->year - 1) . '-' . now()->year)
                    ->options([
                        '2020-2021' => '2020-2021',
                        '2021-2022' => '2021-2022',
                        '2022-2023' => '2022-2023',
                        '2023-2024' => '2023-2024',
                        '2024-2025' => '2024-2025',
                        '2025-2026' => '2025-2026',
                        '2026-2027' => '2026-2027',
                        '2027-2028' => '2027-2028',
                        '2028-2029' => '2028-2029',
                        '2029-2030' => '2029-2030',
                        '2030-2031' => '2030-2031',
                        '2031-2032' => '2031-2032',
                        '2032-2033' => '2032-2033',
                        '2033-2034' => '2033-2034',
                        '2034-2035' => '2034-2035',
                        '2035-2036' => '2035-2036',
                        '2036-2037' => '2036-2037',
                        '2037-2038' => '2037-2038',
                    ]),
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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Project')
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('course'),
                        TextEntry::make('school_year'),
                        TextEntry::make('group_code'),
                        TextEntry::make('status')
                            ->badge(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            MembersRelationManager::class,
            ProjectRelationManager::class,
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
        return Auth::user()?->specialAccess() ?? false;
    }
}
