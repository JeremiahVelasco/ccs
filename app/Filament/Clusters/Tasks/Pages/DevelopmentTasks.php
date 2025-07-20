<?php

namespace App\Filament\Clusters\Tasks\Pages;

use App\Filament\Clusters\Tasks;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DevelopmentTasks extends Page implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-code-bracket';

    protected static string $view = 'filament.clusters.tasks.pages.development-tasks';

    protected static ?string $cluster = Tasks::class;

    public function table(Table $table): Table
    {
        $query = Task::development();

        // Only add project filter if user has a project
        if ($this->hasProject) {
            $query->where('project_id', Auth::user()->group->project->id);
        } else {
            // Return empty query when no project exists
            $query->whereRaw('1 = 0');
        }

        return $table
            ->query($query)
            ->columns([
                TextInputColumn::make('title'),
                TextColumn::make('deadline')
                    ->date()
                    ->color(fn($record) => $record->deadline && abs(Carbon::parse($record->deadline)->diffInDays(now())) <= 5 ? 'danger' : 'success')
                    ->description(fn($record) => $record->deadline ? Carbon::parse($record->deadline)->diffForHumans() : null),
                SelectColumn::make('status')
                    ->options([
                        'To-do' => 'To-do',
                        'In Progress' => 'In Progress',
                        'For Review' => 'For Review',
                        'Done' => 'Done',
                    ])
            ])
            ->actions([
                EditAction::make()
                    ->hiddenLabel()
                    ->tooltip('Edit Task')
                    ->form([
                        Hidden::make('project_id')
                            ->default(Auth::user()->group->id ?? null),
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Select::make('assigned_to')
                            ->label('Assigned To')
                            ->options(User::query()->where('group_id', Auth::user()->group_id)->pluck('name', 'id')->toArray())
                            ->searchable()
                            ->multiple(),
                        Textarea::make('description'),
                        DatePicker::make('deadline'),
                        Select::make('status')
                            ->options([
                                'To-do' => 'To-do',
                                'In Progress' => 'In Progress',
                                'For Review' => 'For Review',
                                'Done' => 'Done',
                            ]),
                        Hidden::make('type')
                            ->default('development'),
                    ]),

                DeleteAction::make()
                    ->tooltip('Delete Task')
                    ->hiddenLabel()
            ])
            ->headerActions([
                CreateAction::make()
                    ->form([
                        Hidden::make('project_id')
                            ->default(Auth::user()->group->project->id ?? null),
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Select::make('assigned_to')
                            ->label('Assigned To')
                            ->options(User::query()->where('group_id', Auth::user()->group_id)->pluck('name', 'id')->toArray())
                            ->searchable()
                            ->multiple(),
                        Textarea::make('description'),
                        DatePicker::make('deadline'),
                        Hidden::make('status')
                            ->default('To-do'),
                        Hidden::make('type')
                            ->default('development'),
                    ]),
            ])
            ->bulkActions([
                //
            ]);
    }

    public function getHasProjectProperty()
    {
        return Auth::user()->hasProject();
    }
}
