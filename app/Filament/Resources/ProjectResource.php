<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Filament\Resources\ProjectResource\RelationManagers\DevelopmentTasksRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\DocumentationTasksRelationManager;
use App\Models\CriterionGrade;
use App\Models\Group;
use App\Models\Project;
use App\Models\ProjectGrade;
use App\Models\Rubric;
use App\Models\RubricCriteria;
use App\Models\User;
use App\Services\ProjectPredictionService;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Components\View;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;


class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('logo')
                    ->hiddenLabel()
                    ->directory('project-logos')
                    ->avatar()
                    ->columnSpanFull()
                    ->alignCenter(),
                TextInput::make('title'),
                Select::make('group_id')
                    ->label('Group')
                    ->options(Group::all()->pluck('name', 'id')),
                Textarea::make('description')
                    ->helperText('Maximum: 150 characters')
                    ->maxLength(150)
                    ->rows(2)
                    ->columnSpanFull(),
                DatePicker::make('deadline')
                    ->label('Deadline')
                    ->default(now()->addDays(30)),
                Select::make('panelists')
                    ->options(
                        User::whereHas('roles', function ($query) {
                            $query->where('name', 'faculty');
                        })->pluck('name', 'id')
                    )
                    ->multiple()
                    ->maxItems(3),
                Section::make('Details')
                    ->collapsible()
                    ->persistCollapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('status')
                                    ->default('In Progress')
                                    ->options([
                                        'In Progress' => 'In Progress',
                                        'For Review' => 'For Review',
                                        'Done' => 'Done'
                                    ]),
                                TextInput::make('progress')
                                    ->disabled()
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->suffix('%'),
                                TextInput::make('final_grade')
                                    ->numeric()
                                    ->maxValue(100),
                                TagsInput::make('awards')
                                    ->suggestions([
                                        '🏆 Best Capstone',
                                        '💡 Most Innovative',
                                        '🖥️ Best Web App',
                                        '📱 Best Mobile App'
                                    ])
                            ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('status')
                    ->badge()
                    ->description(fn(Project $record) => round($record->progress() * 100) . '%'),
                TextColumn::make('title')
                    ->description(fn(Project $record) => $record->group->name),
                TextColumn::make('panelist_status')
                    ->label('Panelists')
                    ->badge()
                    ->color(fn(string $state): string => $state === 'Complete' ? 'success' : 'danger'),
                TextColumn::make('awards'),
                TextColumn::make('final_grade'),
                TextColumn::make('deadline')
                    ->label('Deadline')
                    ->date('d-m-Y'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->tooltip('View Project')
                    ->hiddenLabel(),
                Tables\Actions\EditAction::make()
                    ->tooltip('Edit Project')
                    ->hiddenLabel(),
                Action::make('Grade')
                    ->disabled(fn(Model $project) => !in_array(Auth::id(), $project->panelists ?? []))
                    ->tooltip('Grade Project')
                    ->hiddenLabel()
                    ->color('warning')
                    ->icon('heroicon-m-check-badge')
                    ->form(function () {
                        // Get available rubrics
                        $rubrics = Rubric::where('is_active', true)->pluck('name', 'id');

                        return [
                            Select::make('rubric_id')
                                ->label('Select Rubric')
                                ->options($rubrics)
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(fn(callable $set) => $set('criteria', [])),

                            Textarea::make('remarks')
                                ->label('Overall Remarks')
                                ->rows(3),

                            Forms\Components\Placeholder::make('rubric_grading')
                                ->visible(fn(callable $get) => filled($get('rubric_id')))
                                ->content(function (callable $get) {
                                    $rubricId = $get('rubric_id');
                                    if (!$rubricId) return '';

                                    $rubric = Rubric::with(['sections.criteria.scaleLevels'])
                                        ->find($rubricId);

                                    return new \Illuminate\Support\HtmlString(
                                        view('filament.forms.components.rubric-criteria-grading', [
                                            'rubric' => $rubric,
                                        ])->render()
                                    );
                                }),
                        ];
                    })

                    ->action(function (array $data, Project $project) {
                        $rubricId = $data['rubric_id'];
                        $remarks = $data['remarks'] ?? null;
                        $criteriaGrades = $data['criteria'] ?? [];

                        // Calculate total score from all criteria
                        $totalScore = collect($criteriaGrades)->sum('score');

                        // Create the main project grade record
                        $projectGrade = ProjectGrade::create([
                            'project_id' => $project->id,
                            'rubric_id' => $rubricId,
                            'panel_id' => Auth::id(),
                            'total_score' => $totalScore,
                            'remarks' => $remarks,
                        ]);

                        // Create individual criterion grades
                        foreach ($criteriaGrades as $criterionId => $gradeData) {
                            CriterionGrade::create([
                                'project_grade_id' => $projectGrade->id,
                                'rubric_criterion_id' => $criterionId,
                                'score' => $gradeData['score'] ?? 0,
                                'remarks' => $gradeData['comments'] ?? null,
                            ]);
                        }

                        // Update the project's final grade
                        $project->update([
                            'final_grade' => ProjectGrade::where('project_id', $project->id)
                                ->avg('total_score'),
                        ]);

                        Notification::make()
                            ->title('Project graded successfully')
                            ->body("Total Score: {$totalScore}")
                            ->success()
                            ->send();
                    })
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
                Infolists\Components\Section::make('Project Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('title')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('group.name')
                            ->label('Group'),
                        Infolists\Components\TextEntry::make('description'),
                        Infolists\Components\TextEntry::make('status')
                            ->badge(),
                        Infolists\Components\TextEntry::make('progress')
                            ->suffix('%')
                            ->badge()
                            ->color(
                                fn(string $state): string =>
                                $state === '100' ? 'success' : ((intval($state) >= 50) ? 'warning' : 'danger')
                            ),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('AI-Powered Project Analysis')
                    ->icon('heroicon-m-cpu-chip')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('completion_prediction')
                                    ->label('Completion Probability')
                                    ->state(function ($record) {
                                        $service = app(ProjectPredictionService::class);
                                        $prediction = $service->predictCompletion($record);
                                        return $prediction['percentage'] . '%';
                                    })
                                    ->badge()
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                    ->color(function ($record) {
                                        $service = app(ProjectPredictionService::class);
                                        $prediction = $service->predictCompletion($record);
                                        return match ($prediction['risk_level']) {
                                            'low' => 'success',
                                            'medium' => 'warning',
                                            'high' => 'danger',
                                            'critical' => 'danger',
                                            default => 'gray'
                                        };
                                    }),

                                Infolists\Components\TextEntry::make('risk_level')
                                    ->label('Risk Assessment')
                                    ->state(function ($record) {
                                        $service = app(ProjectPredictionService::class);
                                        $prediction = $service->predictCompletion($record);
                                        return ucfirst($prediction['risk_level']) . ' Risk';
                                    })
                                    ->badge()
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                    ->color(function ($record) {
                                        $service = app(ProjectPredictionService::class);
                                        $prediction = $service->predictCompletion($record);
                                        return match ($prediction['risk_level']) {
                                            'low' => 'success',
                                            'medium' => 'warning',
                                            'high' => 'danger',
                                            'critical' => 'danger',
                                            default => 'gray'
                                        };
                                    }),
                            ]),

                        Infolists\Components\TextEntry::make('last_prediction_update')
                            ->label('Last Updated')
                            ->state(function ($record) {
                                return $record->last_prediction_at
                                    ? $record->last_prediction_at->diffForHumans()
                                    : 'Never';
                            })
                            ->color('gray'),

                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('task_progress_feature')
                                    ->label('Task Progress')
                                    ->state(function ($record) {
                                        $service = app(ProjectPredictionService::class);
                                        $features = $service->calculateProjectFeatures($record);
                                        return ['Low', 'Medium', 'High'][$features['task_progress']];
                                    })
                                    ->badge()
                                    ->color(function ($record) {
                                        $service = app(ProjectPredictionService::class);
                                        $features = $service->calculateProjectFeatures($record);
                                        return match ($features['task_progress']) {
                                            2 => 'success',
                                            1 => 'warning',
                                            0 => 'danger',
                                            default => 'gray'
                                        };
                                    }),

                                Infolists\Components\TextEntry::make('team_collaboration_feature')
                                    ->label('Team Collaboration')
                                    ->state(function ($record) {
                                        $service = app(ProjectPredictionService::class);
                                        $features = $service->calculateProjectFeatures($record);
                                        return ['Poor', 'Good', 'Excellent'][$features['team_collaboration']];
                                    })
                                    ->badge()
                                    ->color(function ($record) {
                                        $service = app(ProjectPredictionService::class);
                                        $features = $service->calculateProjectFeatures($record);
                                        return match ($features['team_collaboration']) {
                                            2 => 'success',
                                            1 => 'warning',
                                            0 => 'danger',
                                            default => 'gray'
                                        };
                                    }),

                                Infolists\Components\TextEntry::make('faculty_approval_feature')
                                    ->label('Faculty Approval')
                                    ->state(function ($record) {
                                        $service = app(ProjectPredictionService::class);
                                        $features = $service->calculateProjectFeatures($record);
                                        return $features['faculty_approval'] ? 'Approved' : 'Pending';
                                    })
                                    ->badge()
                                    ->color(function ($record) {
                                        $service = app(ProjectPredictionService::class);
                                        $features = $service->calculateProjectFeatures($record);
                                        return $features['faculty_approval'] ? 'success' : 'warning';
                                    }),

                                Infolists\Components\TextEntry::make('timeline_adherence_feature')
                                    ->label('Timeline Status')
                                    ->state(function ($record) {
                                        $service = app(ProjectPredictionService::class);
                                        $features = $service->calculateProjectFeatures($record);
                                        return ['Behind', 'On Track', 'Ahead'][$features['timeline_adherence']];
                                    })
                                    ->badge()
                                    ->color(function ($record) {
                                        $service = app(ProjectPredictionService::class);
                                        $features = $service->calculateProjectFeatures($record);
                                        return match ($features['timeline_adherence']) {
                                            2 => 'success',
                                            1 => 'info',
                                            0 => 'danger',
                                            default => 'gray'
                                        };
                                    }),
                            ]),

                        Infolists\Components\TextEntry::make('ai_recommendations')
                            ->label('AI Recommendations')
                            ->state(function ($record) {
                                $service = app(ProjectPredictionService::class);
                                $recommendations = $service->getRecommendations($record);

                                if (empty($recommendations)) {
                                    return '✅ Project is on track! Keep up the good work.';
                                }

                                return '• ' . implode("\n• ", $recommendations);
                            })
                            ->listWithLineBreaks()
                            ->color('info'),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('Panelists & Grading')
                    ->schema([
                        Infolists\Components\TextEntry::make('panelists')
                            ->state(function ($record) {
                                if (empty($record->panelists)) {
                                    return 'No panelists assigned';
                                }

                                $panelists = User::whereIn('id', $record->panelists)->pluck('name');
                                return $panelists->implode(', ');
                            }),
                        Infolists\Components\TextEntry::make('final_grade')
                            ->badge()
                            ->color(
                                fn(?string $state): string =>
                                $state ? (floatval($state) >= 75 ? 'success' : 'warning') : 'gray'
                            ),
                        Infolists\Components\TextEntry::make('awards')
                            ->listWithLineBreaks(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            DocumentationTasksRelationManager::class,
            DevelopmentTasksRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'view' => Pages\ViewProject::route('/{record}'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        // Allow access to individual projects (for repository)
        if (request()->routeIs('filament.resources.projects.view')) {
            return true;
        }

        // Only allow users with view_any_project permission to access the Projects page
        return Auth::user()->can('view_any_project');
    }
}
