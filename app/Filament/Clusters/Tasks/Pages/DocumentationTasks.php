<?php

namespace App\Filament\Clusters\Tasks\Pages;

use App\Filament\Clusters\Tasks;
use App\Filament\Clusters\Tasks\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class DocumentationTasks extends Page implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string $view = 'filament.clusters.tasks.pages.documentation-tasks';

    protected static ?string $cluster = Tasks::class;

    public function table(Table $table): Table
    {
        return $table
            ->striped()
            ->query(Task::documentation()->where('project_id', Auth::user()->group->project->id))
            ->paginated(false)
            ->recordUrl(
                fn(Model $task): string => TaskResource::getUrl('edit', ['record' => $task->id]),
            )
            ->columns([
                TextColumn::make('title'),
                TextColumn::make('deadline')
                    ->date(),
                SelectColumn::make('assigned_to')
                    ->disabled(!Auth::user()->isLeader())
                    ->options(User::query()->where('group_id', Auth::user()->group_id)->pluck('name', 'id')->toArray()),
                SelectColumn::make('status')
                    ->options([
                        'To-do' => 'To-do',
                        'In Progress' => 'In Progress',
                        'For Review' => 'For Review',
                        'Approved' => 'Approved',
                    ])
                    ->disabled(fn($record) => $record->status === 'Approved'),
            ])
            ->actions([
                Action::make('upload')
                    ->disabled(fn($record) => !empty($record->file_path))
                    ->visible(fn($record) => empty($record->file_path))
                    ->tooltip('Upload File')
                    ->hiddenLabel()
                    ->form([
                        FileUpload::make('file')
                            ->label('Upload File')
                            ->disk('public')
                            ->directory('task-files')
                    ])
                    ->action(function ($record, $data) {
                        $record->file_path = $data['file'];
                        $record->save();
                    })
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary'),
            ]);
    }
}
