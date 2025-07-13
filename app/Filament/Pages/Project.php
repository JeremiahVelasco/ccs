<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Slider;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Project as ProjectModel;
use App\Models\Group;
use App\Services\ProjectPredictionService;

class Project extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static string $view = 'filament.pages.project';

    protected static ?string $navigationGroup = 'My Group';

    public ?array $data = [];

    public $project = null;
    public $group = null;
    public $hasProject = false;

    public static function canAccess(): bool
    {
        return Auth::user()->isStudent();
    }

    public function mount(): void
    {
        // Get the user's group
        $this->group = Auth::user()->group;

        // Check if user's group has a project
        if ($this->group) {
            $this->project = $this->group->project;
            $this->hasProject = (bool) $this->project;

            if ($this->hasProject) {
                $this->form->fill([
                    'title' => $this->project->title,
                    'logo' => $this->project->logo,
                    'description' => $this->project->description,
                    'status' => $this->project->status,
                    'deadline' => $this->project->deadline,
                    'progress' => $this->project->progress,
                    'completion_probability' => $this->project->completion_probability,
                ]);
            }
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('logo')
                    ->image()
                    ->avatar()
                    ->hiddenLabel()
                    ->alignCenter()
                    ->disk('public')
                    ->downloadable()
                    ->previewable()
                    ->directory('project-logos')
                    ->visibility('public')
                    ->imagePreviewHeight('100'),

                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Enter project title'),

                Textarea::make('description')
                    ->required()
                    ->maxLength(1000)
                    ->placeholder('Describe your project')
                    ->rows(5),
            ])
            ->statePath('data');
    }

    public function createProject()
    {
        // Validate form
        $data = $this->form->getState();

        // Check if user is in a group
        if (!$this->group) {
            Notification::make()
                ->title('Error')
                ->body('You must be in a group to create a project.')
                ->danger()
                ->send();

            return;
        }

        // Check if group already has a project
        if ($this->hasProject) {
            Notification::make()
                ->title('Error')
                ->body('Your group already has a project.')
                ->danger()
                ->send();

            return;
        }

        // Create new project
        DB::beginTransaction();

        try {
            $project = ProjectModel::create([
                'title' => $data['title'],
                'logo' => $data['logo'],
                'description' => $data['description'],
                'leader_id' => Auth::user()->id,
                'status' => 'In Progress',
                'group_id' => $this->group->id,
                'awards' => [],
            ]);

            DB::commit();

            $this->project = $project;
            $this->hasProject = true;

            Notification::make()
                ->title('Success')
                ->body('Project created successfully!')
                ->success()
                ->send();
        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Error')
                ->body('An error occurred while creating the project. Please try again: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function updateProject()
    {
        // Validate form
        $data = $this->form->getState();

        // Check if project exists
        if (!$this->project) {
            Notification::make()
                ->title('Error')
                ->body('No project found to update.')
                ->danger()
                ->send();

            return;
        }

        // Update project
        DB::beginTransaction();

        try {
            $updateData = [
                'title' => $data['title'],
                'description' => $data['description'],
            ];

            // Only update logo if a new one is uploaded
            if ($data['logo'] && $data['logo'] !== $this->project->logo) {
                $updateData['logo'] = $data['logo'];
            }

            $this->project->update($updateData);

            DB::commit();

            Notification::make()
                ->title('Success')
                ->body('Project updated successfully!')
                ->success()
                ->send();
        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Error')
                ->body('An error occurred while updating the project. Please try again: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function refreshPrediction()
    {
        $predictionService = new ProjectPredictionService();
        $prediction = $predictionService->predictCompletion($this->project);

        $this->project->update([
            'completion_probability' => $prediction['probability'],
            'last_prediction_at' => now(),
            'prediction_version' => ($this->project->prediction_version ?? 0) + 1
        ]);

        Notification::make()
            ->title('Success')
            ->body('Prediction refreshed successfully!')
            ->success()
            ->send();
    }

    public function uploadFile() {}
}
