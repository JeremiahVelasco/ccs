<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Filament\Resources\ProjectResource\RelationManagers\DevelopmentTasksRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\DocumentationTasksRelationManager;

use App\Models\CriterionGrade;
use App\Models\Group;
use App\Models\GroupRubricEvaluation;
use App\Models\IndividualRubricEvaluation;
use App\Models\Project;
use App\Models\ProjectGrade;
use App\Models\Rubric;
use App\Models\RubricCriteria;
use App\Models\User;
use App\Services\GradingService;
use App\Services\ProjectPredictionService;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


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
                                    ->numeric(2)
                                    ->suffix('%'),
                                TextInput::make('final_grade')
                                    ->disabled()
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
                TextColumn::make('title')
                    ->searchable()
                    ->description(fn(Project $record) => $record->group->name),
                TextColumn::make('completion_probability')
                    ->label('Completion Probability')
                    ->state(fn(Project $record) => $record->completion_probability * 100 . '%')
                    ->numeric(2),
                TextColumn::make('status')
                    ->badge()
                    ->description(fn(Project $record) => $record->progressAttribute() . '%'),
                TextColumn::make('panelist_status')
                    ->label('Panelists')
                    ->badge()
                    ->color(fn(string $state): string => $state === 'Complete' ? 'success' : 'danger'),
                TextColumn::make('deadline')
                    ->date()
                    ->label('Deadline'),
                TextColumn::make('awards'),
                TextColumn::make('final_grade')
                    ->state(fn(Project $record) => $record->final_grade ?? 'N/A')
                    ->numeric(2)
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('panelist_status')
                    ->options([
                        'Complete' => 'Complete',
                        'Incomplete' => 'Incomplete',
                    ])
                    ->label('Panelists Status'),
                Tables\Filters\SelectFilter::make('awards')
                    ->options([
                        '🏆 Best Capstone' => '🏆 Best Capstone',
                        '💡 Most Innovative' => '💡 Most Innovative',
                        '🖥️ Best Web App' => '🖥️ Best Web App',
                        '📱 Best Mobile App' => '📱 Best Mobile App',
                    ])
                    ->label('Awards'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'In Progress' => 'In Progress',
                        'For Review' => 'For Review',
                        'Done' => 'Done',
                    ])
                    ->label('Status'),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->tooltip('View Project')
                    ->hiddenLabel(),
                Tables\Actions\EditAction::make()
                    ->tooltip('Edit Project')
                    ->hiddenLabel(),
                Action::make('Grade')
                    ->disabled(fn(Model $project) => !app(GradingService::class)->canGradeProject($project, Auth::user()))
                    ->tooltip('Grade Project')
                    ->hiddenLabel()
                    ->color('warning')
                    ->icon('heroicon-m-check-badge')
                    ->url(fn(Model $project) => route('grading.component', $project))
                    ->openUrlInNewTab()
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
                        Infolists\Components\TextEntry::make('panelists')
                            ->state(function ($record) {
                                if (empty($record->panelists)) {
                                    return 'No panelists assigned';
                                }

                                $panelists = User::whereIn('id', $record->panelists)->pluck('name');
                                return $panelists->implode(', ');
                            }),
                        Infolists\Components\TextEntry::make('awards')
                            ->listWithLineBreaks(),
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

                Infolists\Components\Section::make('Grading')
                    ->schema([
                        Infolists\Components\TextEntry::make('final_grade')
                            ->badge()
                            ->color(
                                fn(?string $state): string =>
                                $state ? (floatval($state) >= 75 ? 'success' : 'warning') : 'gray'
                            ),
                        Infolists\Components\Section::make('Member Individual Grades')
                            ->schema([
                                Infolists\Components\TextEntry::make('member_individual_grades')
                                    ->label('Member Individual Grades')
                                    ->state(function ($record) {
                                        // Get all group members
                                        if (!$record->group) {
                                            return 'No group found';
                                        }

                                        $groupMembers = $record->group->members;

                                        if (!$groupMembers || $groupMembers->isEmpty()) {
                                            return 'No group members found';
                                        }

                                        $memberGrades = [];

                                        foreach ($groupMembers as $member) {
                                            // Get all individual rubric evaluations for this member in this project
                                            $evaluations = IndividualRubricEvaluation::where('project_id', $record->id)
                                                ->where('student_id', $member->id)
                                                ->get();

                                            if ($evaluations->isNotEmpty()) {
                                                // Calculate average scores for each criterion
                                                $criteriaAverages = [
                                                    'subject_mastery' => $evaluations->avg('subject_mastery'),
                                                    'ability_to_answer_questions' => $evaluations->avg('ability_to_answer_questions'),
                                                    'delivery' => $evaluations->avg('delivery'),
                                                    'verbal_and_nonverbal_ability' => $evaluations->avg('verbal_and_nonverbal_ability'),
                                                    'grooming' => $evaluations->avg('grooming'),
                                                ];

                                                // Calculate overall average
                                                $overallAverage = collect($criteriaAverages)->avg();

                                                $memberGrades[] = sprintf(
                                                    '👤 %s: %.2f/5.0
    📚 Subject Mastery: %.2f | 💬 Q&A: %.2f | 🎤 Delivery: %.2f
    🗣️ Verbal Skills: %.2f | ✨ Grooming: %.2f',
                                                    $member->name,
                                                    $overallAverage,
                                                    $criteriaAverages['subject_mastery'],
                                                    $criteriaAverages['ability_to_answer_questions'],
                                                    $criteriaAverages['delivery'],
                                                    $criteriaAverages['verbal_and_nonverbal_ability'],
                                                    $criteriaAverages['grooming']
                                                );
                                            } else {
                                                $memberGrades[] = $member->name . ': No evaluations yet';
                                            }
                                        }

                                        return $memberGrades;
                                    })
                                    ->listWithLineBreaks(),
                            ])
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
