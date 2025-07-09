<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\User;
use App\Services\GradingService;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.grading')]
class GradingComponent extends Component
{
    public ?array $data = [];
    public Project $project;
    public ?User $panelist = null;

    protected ?GradingService $gradingService = null;

    protected function getGradingService(): GradingService
    {
        if (!$this->gradingService) {
            $this->gradingService = app(GradingService::class);
        }
        return $this->gradingService;
    }

    public function mount(Project $project): void
    {
        $this->project = $project;
        $this->panelist = Auth::user();

        // Check if user can grade this project
        if (!$this->getGradingService()->canGradeProject($this->project, $this->panelist)) {
            abort(403, 'You are not authorized to grade this project.');
        }

        // Load existing evaluation if any
        $existingEvaluation = $this->getGradingService()->getExistingEvaluation($this->project, $this->panelist);

        if ($existingEvaluation) {
            // Pre-populate with existing data
            $this->data = $existingEvaluation->toArray();
            unset($this->data['id'], $this->data['created_at'], $this->data['updated_at']);
        } else {
            // Initialize with defaults
            $this->data = [
                'documentation_score' => 0,
                'prototype_score' => 0,
                'presentation_score' => 0,
                'remarks' => '',
            ];

            // Initialize all criteria with 0
            $criteria = $this->getGradingService()->getGradingCriteria();
            foreach ($criteria['detailed'] as $key => $criterion) {
                $this->data[$key] = 0;
            }
        }
    }

    public function submitGrade(): void
    {
        try {
            // Validate the data
            $validationErrors = $this->getGradingService()->validateGradingData($this->data);
            if (!empty($validationErrors)) {
                foreach ($validationErrors as $field => $message) {
                    $this->addError($field, $message);
                }
                return;
            }

            // Create or update the evaluation
            $evaluation = $this->getGradingService()->createGradingEvaluation(
                $this->project,
                $this->panelist,
                $this->data
            );

            // Calculate final scores for notification
            $scores = $this->getGradingService()->calculateScores($this->data);

            Notification::make()
                ->title('Project graded successfully')
                ->body("Summary Score: {$scores['summary_total']} | Weighted Score: {$scores['weighted_score']}")
                ->success()
                ->send();

            // Redirect back to projects page
            $this->redirect(url('/projects'));
        } catch (\Exception $e) {
            Log::error('Error grading project', [
                'project_id' => $this->project->id,
                'user_id' => $this->panelist->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Notification::make()
                ->title('Error')
                ->body('There was an error saving the grade. Please try again.')
                ->danger()
                ->send();
        }
    }

    public function render(): View
    {
        return view('livewire.grading-component', [
            'criteria' => $this->getGradingService()->getGradingCriteria(),
            'totalWeight' => $this->getGradingService()->getTotalWeight(),
        ]);
    }
}
