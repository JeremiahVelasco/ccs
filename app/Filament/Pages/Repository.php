<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\Tasks\Resources\TaskResource;
use App\Filament\Resources\ProjectResource;
use App\Models\Group;
use App\Models\Project;
use App\Models\Task;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Dashboard;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Repository extends Page implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-server-stack';

    protected static string $view = 'filament.pages.repository';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Project::query()
                    ->with(['group', 'tasks'])
                    ->whereHas('tasks', function ($query) {
                        $query->where('title', 'Final Documentation')
                            ->where('type', 'documentation')
                            ->whereNotNull('file_path')
                            ->where('file_path', '!=', '');
                    })
            )
            ->recordUrl(
                fn(Model $project): string => ProjectResource::getUrl('view', ['record' => $project->id]),
            )
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('description')
                    ->searchable(),
                TextColumn::make('final_grade')
                    ->sortable(),
                TextColumn::make('awards'),
                TextColumn::make('group.course')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('school_year')
                    ->label('School Year')
                    ->default((now()->year - 1) . '-' . now()->year)
                    ->options(function () {
                        return Group::distinct()
                            ->whereNotNull('school_year')
                            ->pluck('school_year', 'school_year')
                            ->sort()
                            ->toArray();
                    })
                    ->query(function ($query, $data) {
                        return $query->when($data, function ($query, $data) {
                            $query->whereHas('group', function ($q) use ($data) {
                                $q->where('school_year', $data);
                            });
                        });
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Action::make('view')
                    ->hiddenLabel()
                    ->url(function (Model $project): string {
                        $finalDocTask = $project->tasks()
                            ->where('title', 'Final Documentation')
                            ->where('type', 'documentation')
                            ->whereNotNull('file_path')
                            ->first();

                        return $finalDocTask ? Storage::url($finalDocTask->file_path) : '#';
                    })
                    ->openUrlInNewTab()
                    ->icon('heroicon-m-eye')
                    ->color('info')
                    ->tooltip('View Final Documentation')
                    ->visible(function (Model $project): bool {
                        $finalDocTask = $project->tasks()
                            ->where('title', 'Final Documentation')
                            ->where('type', 'documentation')
                            ->whereNotNull('file_path')
                            ->first();

                        return $finalDocTask && Storage::disk('public')->exists($finalDocTask->file_path);
                    }),
                Action::make('download')
                    ->hiddenLabel()
                    ->url(function (Model $project): string {
                        $finalDocTask = $project->tasks()
                            ->where('title', 'Final Documentation')
                            ->where('type', 'documentation')
                            ->whereNotNull('file_path')
                            ->first();

                        return $finalDocTask ? Storage::url($finalDocTask->file_path) : '#';
                    })
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('success')
                    ->tooltip('Download Final Documentation')
                    ->visible(function (Model $project): bool {
                        $finalDocTask = $project->tasks()
                            ->where('title', 'Final Documentation')
                            ->where('type', 'documentation')
                            ->whereNotNull('file_path')
                            ->first();

                        return $finalDocTask && Storage::disk('public')->exists($finalDocTask->file_path);
                    }),
            ]);
    }

    public static function canAccess(): bool
    {

        return true;
    }
}
