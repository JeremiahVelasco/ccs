<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\User;
use App\Models\IndividualRubricEvaluation;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.grading')]
class IndividualGradingComponent extends Component
{
    public ?array $scores = [];
    public Project $project;
    public ?User $panelist = null;
    public $groupMembers = [];

    public function mount(Project $project): void
    {
        $this->project = $project;
        $this->panelist = Auth::user();

        // Get group members
        $this->groupMembers = $this->project->group->members ?? collect();

        // Initialize scores array
        $this->initializeScores();
    }

    protected function initializeScores(): void
    {
        $criteria = $this->getIndividualCriteria();

        // Load existing evaluations if any
        $existingEvaluations = IndividualRubricEvaluation::where([
            'panel_id' => $this->panelist->id,
            'project_id' => $this->project->id,
        ])->get()->keyBy('student_id');

        foreach ($this->groupMembers as $member) {
            foreach ($criteria as $criterionKey => $criterion) {
                if (isset($existingEvaluations[$member->id])) {
                    // Pre-populate with existing data
                    $this->scores[$member->id][$criterionKey] = $existingEvaluations[$member->id]->$criterionKey ?? 0;
                } else {
                    // Initialize with 0
                    $this->scores[$member->id][$criterionKey] = 0;
                }
            }
        }
    }

    public function getIndividualCriteria(): array
    {
        return [
            'subject_mastery' => [
                'name' => 'Subject Mastery',
                'weight' => 3,
                'descriptions' => [
                    5 => 'Student discusses the subject with enough information, provides supporting details and gives specific examples.',
                    4 => 'Student discusses the subject with enough information and supporting details.',
                    3 => 'Student discusses the subject with enough information.',
                    2 => 'Student discusses the subject with very minimal details during the discussion.',
                    1 => 'Student has no subject mastery at all.'
                ]
            ],
            'ability_to_answer_questions' => [
                'name' => 'Ability to Answer Questions',
                'weight' => 2,
                'descriptions' => [
                    5 => 'Student can answer all questions about the subject and can explain thoroughly.',
                    4 => 'Student can answer most questions about the subject.',
                    3 => 'Student can answer some questions about the subject.',
                    2 => 'Student can answer few questions about the subject.',
                    1 => 'Student cannot answer any question about the subject.'
                ]
            ],
            'delivery' => [
                'name' => 'Delivery',
                'weight' => 2,
                'descriptions' => [
                    5 => 'Student shows very excellent gestures and expressions to convey ideas.',
                    4 => 'Student shows very good gestures and expressions to convey ideas.',
                    3 => 'Student shows good gestures and expressions to convey ideas.',
                    2 => 'Student shows gestures and expressions that needs improvement to convey ideas.',
                    1 => 'Student show poor gestures and expressions to convey ideas.'
                ]
            ],
            'verbal_and_nonverbal_ability' => [
                'name' => 'Verbal and Non-verbal Ability',
                'weight' => 2,
                'descriptions' => [
                    5 => 'Correct grammar, pronunciation, choice of words and use of the English language in general are exceptional.',
                    4 => 'Correct grammar, pronunciation, choice of words and use of the English language in general are good.',
                    3 => 'Correct grammar, pronunciation, choice of words and use of the English language in general are acceptable.',
                    2 => 'Correct grammar, pronunciation, choice of words, and use of the English language are acceptable, with some flaws.',
                    1 => 'Correct grammar, pronunciation, choice of words and use of the English language in general are rarely observed.'
                ]
            ],
            'grooming' => [
                'name' => 'Grooming & Professional Appearance',
                'weight' => 1,
                'descriptions' => [
                    5 => 'Student is very well groomed and shows very professional appearance.',
                    4 => 'Student is well groomed and shows professional appearance.',
                    3 => 'Student is adequately groomed and shows appropriate appearance.',
                    2 => 'Student is poorly groomed and shows unprofessional appearance.',
                    1 => 'Student has very poor grooming and inappropriate appearance.'
                ]
            ]
        ];
    }

    public function updateScore($memberId, $criterion, $score): void
    {
        $score = (int) $score;

        // Validate score range
        if ($score < 0 || $score > 5) {
            return;
        }

        $this->scores[$memberId][$criterion] = $score;
    }

    public function submitIndividualGrades(): void
    {
        try {
            // Validate that at least some scores are provided
            $hasScores = false;
            foreach ($this->scores as $memberId => $memberScores) {
                foreach ($memberScores as $score) {
                    if ($score > 0) {
                        $hasScores = true;
                        break 2;
                    }
                }
            }

            if (!$hasScores) {
                Notification::make()
                    ->title('No scores provided')
                    ->body('Please provide at least one score before submitting.')
                    ->warning()
                    ->send();
                return;
            }

            DB::transaction(function () {
                foreach ($this->groupMembers as $member) {
                    $memberScores = $this->scores[$member->id] ?? [];

                    // Only save if there are scores for this member
                    $hasValidScores = false;
                    foreach ($memberScores as $score) {
                        if ($score > 0) {
                            $hasValidScores = true;
                            break;
                        }
                    }

                    if (!$hasValidScores) {
                        continue;
                    }

                    // Create or update evaluation for this member
                    IndividualRubricEvaluation::updateOrCreate(
                        [
                            'panel_id' => $this->panelist->id,
                            'project_id' => $this->project->id,
                            'student_id' => $member->id,
                        ],
                        [
                            'subject_mastery' => $memberScores['subject_mastery'] ?? 0,
                            'ability_to_answer_questions' => $memberScores['ability_to_answer_questions'] ?? 0,
                            'delivery' => $memberScores['delivery'] ?? 0,
                            'verbal_and_nonverbal_ability' => $memberScores['verbal_and_nonverbal_ability'] ?? 0,
                            'grooming' => $memberScores['grooming'] ?? 0,
                        ]
                    );
                }
            });

            Notification::make()
                ->title('Individual grades submitted successfully')
                ->body('All individual evaluations have been saved.')
                ->success()
                ->send();

            // Redirect back to projects page
            $this->redirect(url('/projects'));
        } catch (\Exception $e) {
            Log::error('Error submitting individual grades', [
                'project_id' => $this->project->id,
                'panelist_id' => $this->panelist->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Notification::make()
                ->title('Error')
                ->body('There was an error saving the individual grades. Please try again.')
                ->danger()
                ->send();
        }
    }

    public function getTotalWeight(): int
    {
        $criteria = $this->getIndividualCriteria();
        return array_sum(array_column($criteria, 'weight'));
    }

    public function calculateMemberAverage($memberId): float
    {
        $memberScores = $this->scores[$memberId] ?? [];
        $validScores = array_filter($memberScores, fn($score) => $score > 0);

        if (empty($validScores)) {
            return 0;
        }

        return round(array_sum($validScores) / count($validScores), 2);
    }

    public function render(): View
    {
        return view('livewire.individual-grading-component', [
            'criteria' => $this->getIndividualCriteria(),
            'totalWeight' => $this->getTotalWeight(),
        ]);
    }
}
