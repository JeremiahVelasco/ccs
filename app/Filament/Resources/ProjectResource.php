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
use App\Models\RubricCriterion;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Components\View;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
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
                Select::make('panelists')
                    ->columnSpanFull()
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
                TextColumn::make('progress')
                    ->badge()
                    ->suffix('%')
                    ->color(
                        fn(string $state): string =>
                        $state === '100'
                            ? 'success'
                            : ((intval($state) >= 50) ? 'warning' : 'danger')
                    ),
                TextColumn::make('title'),
                TextColumn::make('group.name'),
                TextColumn::make('panelist_status')
                    ->label('Panelists')
                    ->badge()
                    ->color(fn(string $state): string => $state === 'Complete' ? 'success' : 'danger'),
                TextColumn::make('awards'),
                TextColumn::make('final_grade'),
                TextColumn::make('status')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('Grade')
                    ->visible(fn(Model $project) => in_array(Auth::id(), $project->panelists ?? []))
                    ->label('Grade')
                    ->color('warning')
                    ->icon('heroicon-m-check-badge')
                    ->form(function () {
                        // Get available rubrics
                        $rubrics = Rubric::pluck('name', 'id');

                        $form = [
                            Select::make('rubric_id')
                                ->label('Select Rubric')
                                ->options($rubrics)
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(fn(callable $set) => $set('criteria', [])),

                            Textarea::make('remarks')
                                ->label('Overall Remarks')
                                ->rows(3),
                        ];

                        // Add dynamic criteria fields based on selected rubric
                        $form[] = Grid::make()
                            ->visible(fn(callable $get) => filled($get('rubric_id')))
                            ->schema(function (callable $get) {
                                $rubricId = $get('rubric_id');
                                if (!$rubricId) return [];

                                $criteria = RubricCriterion::where('rubric_id', $rubricId)->get();
                                $fields = [];

                                foreach ($criteria as $criterion) {
                                    $fields[] = Section::make($criterion->name)
                                        ->description($criterion->description)
                                        ->extraAttributes([
                                            'class' => 'p-4 bg-gray-50 rounded-lg space-y-3',
                                        ])
                                        ->schema([
                                            Grid::make()
                                                ->schema([
                                                    TextInput::make("criteria.{$criterion->id}.score")
                                                        ->label("Score (Max: {$criterion->max_score})")
                                                        ->helperText("Weight: {$criterion->weight}%")
                                                        ->numeric()
                                                        ->minValue(0)
                                                        ->maxValue($criterion->max_score)
                                                        ->required()
                                                        ->columnSpan(1),

                                                    Textarea::make("criteria.{$criterion->id}.remarks")
                                                        ->label('Remarks')
                                                        ->rows(2)
                                                        ->columnSpan(3),
                                                ])
                                                ->columns(4),
                                        ]);
                                }

                                return $fields;
                            })
                            ->columns(1);

                        return $form;
                    })

                    ->action(function (array $data, Project $project) {
                        $rubricId = $data['rubric_id'];
                        $remarks = $data['remarks'] ?? null;
                        $criteriaGrades = $data['criteria'] ?? [];

                        // Create the main project grade record
                        $totalScore = collect($criteriaGrades)->sum('score');

                        $projectGrade = ProjectGrade::create([
                            'project_id' => $project->id,
                            'rubric_id' => $rubricId,
                            'panel_id' => Auth::id(),
                            'total_score' => $totalScore,
                            'remarks' => $remarks,
                            'graded_at' => now(),
                        ]);

                        // Create individual criterion grades
                        foreach ($criteriaGrades as $criterionId => $gradeData) {
                            CriterionGrade::create([
                                'project_grade_id' => $projectGrade->id,
                                'rubric_criterion_id' => $criterionId,
                                'score' => $gradeData['score'],
                                'remarks' => $gradeData['remarks'] ?? null,
                            ]);
                        }

                        // Update the project's final grade if needed
                        // This will depend on your logic - could be the latest grade, an average, etc.
                        $project->update([
                            'final_grade' => ProjectGrade::where('project_id', $project->id)
                                ->avg('total_score'),
                        ]);

                        Notification::make()
                            ->title('Project graded successfully')
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
