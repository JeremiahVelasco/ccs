<?php

namespace App\Filament\Clusters\Tasks\Pages;

use App\Filament\Clusters\Tasks;
use App\Filament\Clusters\Tasks\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DocumentationTasks extends Page implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string $view = 'filament.clusters.tasks.pages.documentation-tasks';

    protected static ?string $cluster = Tasks::class;

    public function table(Table $table): Table
    {
        $query = Task::documentation();

        // Only add project filter if user has a project
        if ($this->hasProject) {
            $query->where('project_id', Auth::user()->group->project->id);
        } else {
            // Return empty query when no project exists
            $query->whereRaw('1 = 0');
        }

        return $table
            ->striped()
            ->query($query)
            ->paginated(false)
            ->recordUrl(
                fn(Model $task): string => TaskResource::getUrl('edit', ['record' => $task->id]),
            )
            ->columns([
                TextColumn::make('title'),
                TextColumn::make('deadline')
                    ->date()
                    ->color(fn($record) => $record->deadline && abs(Carbon::parse($record->deadline)->diffInDays(now())) <= 5 ? 'danger' : 'success')
                    ->description(fn($record) => $record->deadline ? Carbon::parse($record->deadline)->diffForHumans() : null),
                SelectColumn::make('assigned_to')
                    ->disabled(!Auth::user()->isLeader())
                    ->options(User::query()->where('group_id', Auth::user()->group_id)->pluck('name', 'id')->toArray()),
                SelectColumn::make('status')
                    ->options(function ($record) {
                        $baseOptions = [
                            'To-do' => 'To-do',
                            'In Progress' => 'In Progress',
                            'For Review' => 'For Review',
                        ];

                        // Only include "Approved" if the record already has that status
                        if ($record->status === 'Approved') {
                            $baseOptions['Approved'] = 'Approved';
                        }

                        return $baseOptions;
                    })
                    ->disabled(fn($record) => $record->status === 'Approved'),
                CheckboxColumn::make('file_path')
                    ->label('File Uploaded')
                    ->alignCenter()
                    ->disabled(),
            ])
            ->actions([
                //
            ]);
    }

    public function getHasProjectProperty()
    {
        return Auth::user()->hasProject();
    }
}
