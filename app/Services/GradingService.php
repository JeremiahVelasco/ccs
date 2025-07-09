<?php

namespace App\Services;

use App\Models\GroupRubricEvaluation;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GradingService
{
    /**
     * Get all grading criteria with their weights and descriptions
     */
    public function getGradingCriteria(): array
    {
        // Cache criteria for 1 hour to avoid repeated calculations
        return Cache::remember('grading_criteria', 3600, function () {
            return [
                'summary' => [
                    'documentation_score' => [
                        'name' => 'Documentation',
                        'max_score' => 30,
                        'weight' => 1,
                        'type' => 'summary'
                    ],
                    'prototype_score' => [
                        'name' => 'Prototype',
                        'max_score' => 40,
                        'weight' => 1,
                        'type' => 'summary'
                    ],
                    'presentation_score' => [
                        'name' => 'Presentation',
                        'max_score' => 30,
                        'weight' => 1,
                        'type' => 'summary'
                    ],
                ],
                'detailed' => [
                    'presentation_of_results' => [
                        'name' => 'Presentation of Results',
                        'weight' => 2,
                        'descriptions' => [
                            5 => 'Extremely clear, well-organized, and professionally presented results with excellent visual aids',
                            4 => 'Clear and well-organized presentation with good visual aids and structure',
                            3 => 'Adequately presented results with basic organization and visual support',
                            2 => 'Poorly organized presentation with minimal visual aids and unclear structure',
                            1 => 'Very poor presentation with no organization and no visual aids'
                        ]
                    ],
                    'summary_of_findings' => [
                        'name' => 'Summary of Findings',
                        'weight' => 1,
                        'descriptions' => [
                            5 => 'Comprehensive and insightful summary that captures all key findings effectively',
                            4 => 'Good summary that covers most important findings with clear explanations',
                            3 => 'Adequate summary covering basic findings with some clarity',
                            2 => 'Limited summary with unclear explanations and missing key findings',
                            1 => 'Very poor summary with no clear findings or explanations'
                        ]
                    ],
                    'conclusion' => [
                        'name' => 'Conclusion',
                        'weight' => 1,
                        'descriptions' => [
                            5 => 'Strong, well-supported conclusions that directly address the research objectives',
                            4 => 'Good conclusions with adequate support and clear connections to objectives',
                            3 => 'Basic conclusions with some support and relevance to objectives',
                            2 => 'Weak conclusions with limited support and unclear connections',
                            1 => 'Very poor conclusions with no support or relevance'
                        ]
                    ],
                    'recommendation' => [
                        'name' => 'Recommendation',
                        'weight' => 1,
                        'descriptions' => [
                            5 => 'Excellent, actionable recommendations based on findings with clear implementation steps',
                            4 => 'Good recommendations with practical value and clear basis in findings',
                            3 => 'Adequate recommendations with some practical value and connection to findings',
                            2 => 'Limited recommendations with unclear practical value',
                            1 => 'Very poor recommendations with no practical value or basis'
                        ]
                    ],
                    'content' => [
                        'name' => 'Content',
                        'weight' => 1,
                        'descriptions' => [
                            5 => 'Excellent content depth, accuracy, and relevance with comprehensive coverage',
                            4 => 'Good content with adequate depth and accuracy',
                            3 => 'Satisfactory content with basic coverage and accuracy',
                            2 => 'Limited content with minimal depth and some inaccuracies',
                            1 => 'Very poor content with no depth and significant inaccuracies'
                        ]
                    ],
                    'project_output' => [
                        'name' => 'Project Output',
                        'weight' => 4,
                        'descriptions' => [
                            5 => 'Outstanding project output that exceeds expectations with innovative solutions',
                            4 => 'Excellent project output meeting all requirements with quality execution',
                            3 => 'Good project output meeting basic requirements with adequate execution',
                            2 => 'Limited project output with minimal requirements met',
                            1 => 'Very poor project output with requirements not met'
                        ]
                    ],
                    'relevance_to_specialization' => [
                        'name' => 'Relevance to Specialization',
                        'weight' => 2,
                        'descriptions' => [
                            5 => 'Highly relevant to specialization with deep integration of specialized knowledge',
                            4 => 'Good relevance with adequate integration of specialized concepts',
                            3 => 'Adequate relevance with basic integration of specialization',
                            2 => 'Limited relevance with minimal connection to specialization',
                            1 => 'Very poor relevance with no connection to specialization'
                        ]
                    ],
                    'project_demonstration' => [
                        'name' => 'Project Demonstration',
                        'weight' => 2,
                        'descriptions' => [
                            5 => 'Excellent demonstration with clear explanation and engaging presentation',
                            4 => 'Good demonstration with adequate explanation and presentation',
                            3 => 'Satisfactory demonstration with basic explanation',
                            2 => 'Limited demonstration with unclear explanation',
                            1 => 'Very poor demonstration with no clear explanation'
                        ]
                    ],
                    'consistency' => [
                        'name' => 'Consistency',
                        'weight' => 3,
                        'descriptions' => [
                            5 => 'Excellent consistency in methodology, presentation, and quality throughout',
                            4 => 'Good consistency with minor variations in quality',
                            3 => 'Adequate consistency with some variations',
                            2 => 'Limited consistency with significant variations',
                            1 => 'Very poor consistency with major variations'
                        ]
                    ],
                    'materials' => [
                        'name' => 'Materials',
                        'weight' => 1,
                        'descriptions' => [
                            5 => 'Excellent use of materials with high quality and appropriate selection',
                            4 => 'Good use of materials with adequate quality and selection',
                            3 => 'Satisfactory use of materials with basic quality',
                            2 => 'Limited use of materials with poor quality',
                            1 => 'Very poor use of materials with inappropriate selection'
                        ]
                    ],
                    'manner_of_presentation' => [
                        'name' => 'Manner of Presentation',
                        'weight' => 1,
                        'descriptions' => [
                            5 => 'Outstanding presentation skills with excellent communication and confidence',
                            4 => 'Good presentation skills with clear communication',
                            3 => 'Adequate presentation skills with basic communication',
                            2 => 'Limited presentation skills with unclear communication',
                            1 => 'Very poor presentation skills with no clear communication'
                        ]
                    ],
                    'presentation_of_project_overview' => [
                        'name' => 'Presentation of Project Overview',
                        'weight' => 1,
                        'descriptions' => [
                            5 => 'Excellent overview that clearly explains the project scope and objectives',
                            4 => 'Good overview with adequate explanation of scope and objectives',
                            3 => 'Satisfactory overview with basic explanation',
                            2 => 'Limited overview with unclear explanation',
                            1 => 'Very poor overview with no clear explanation'
                        ]
                    ]
                ]
            ];
        });
    }

    /**
     * Validate grading data
     */
    public function validateGradingData(array $data): array
    {
        $errors = [];
        $criteria = $this->getGradingCriteria();

        // Validate summary scores (only if they have values)
        foreach ($criteria['summary'] as $key => $criterion) {
            if (isset($data[$key]) && $data[$key] !== '' && $data[$key] !== null) {
                $score = floatval($data[$key]);
                if ($score < 0 || $score > $criterion['max_score']) {
                    $errors[$key] = "{$criterion['name']} score must be between 0 and {$criterion['max_score']}";
                }
            }
        }

        // Validate detailed criteria scores (only if they have values > 0)
        foreach ($criteria['detailed'] as $key => $criterion) {
            if (isset($data[$key]) && $data[$key] !== '' && $data[$key] !== null) {
                $score = intval($data[$key]);
                // Allow 0 (empty/ungraded) or valid range 1-5
                if ($score !== 0 && ($score < 1 || $score > 5)) {
                    $errors[$key] = "{$criterion['name']} score must be between 1 and 5 (or 0 for ungraded)";
                }
            }
        }

        return $errors;
    }

    /**
     * Calculate weighted scores and totals
     */
    public function calculateScores(array $data): array
    {
        $criteria = $this->getGradingCriteria();
        $calculations = [
            'summary_total' => 0,
            'weighted_score' => 0,
            'average_score' => 0,
            'completed_count' => 0,
            'total_weight' => 0
        ];

        // Calculate summary total
        foreach ($criteria['summary'] as $key => $criterion) {
            $calculations['summary_total'] += $data[$key] ?? 0;
        }

        // Calculate weighted scores from detailed criteria
        $totalScore = 0;
        $completedCount = 0;

        foreach ($criteria['detailed'] as $key => $criterion) {
            $score = $data[$key] ?? 0;
            if ($score > 0) {
                $completedCount++;
                $totalScore += $score;
                $calculations['weighted_score'] += $score * $criterion['weight'];
            }
            $calculations['total_weight'] += $criterion['weight'];
        }

        $calculations['completed_count'] = $completedCount;
        $calculations['average_score'] = $completedCount > 0 ? $totalScore / $completedCount : 0;

        return $calculations;
    }

    /**
     * Create or update a grading evaluation
     */
    public function createGradingEvaluation(Project $project, User $panelist, array $gradingData): GroupRubricEvaluation
    {
        return DB::transaction(function () use ($project, $panelist, $gradingData) {
            Log::info('Creating grading evaluation', [
                'project_id' => $project->id,
                'panelist_id' => $panelist->id,
                'grading_data' => $gradingData
            ]);

            // Check if evaluation already exists
            $evaluation = GroupRubricEvaluation::where([
                'panel_id' => $panelist->id,
                'project_id' => $project->id,
            ])->first();

            $scores = $this->calculateScores($gradingData);

            $evaluationData = [
                'panel_id' => $panelist->id,
                'project_id' => $project->id,
                'documentation_score' => $gradingData['documentation_score'] ?? 0,
                'prototype_score' => $gradingData['prototype_score'] ?? 0,
                'presentation_score' => $gradingData['presentation_score'] ?? 0,
                'total_summary_score' => $scores['summary_total'],
                'presentation_of_results' => $gradingData['presentation_of_results'] ?? 0,
                'summary_of_findings' => $gradingData['summary_of_findings'] ?? 0,
                'conclusion' => $gradingData['conclusion'] ?? 0,
                'recommendation' => $gradingData['recommendation'] ?? 0,
                'content' => $gradingData['content'] ?? 0,
                'project_output' => $gradingData['project_output'] ?? 0,
                'relevance_to_specialization' => $gradingData['relevance_to_specialization'] ?? 0,
                'project_demonstration' => $gradingData['project_demonstration'] ?? 0,
                'consistency' => $gradingData['consistency'] ?? 0,
                'materials' => $gradingData['materials'] ?? 0,
                'manner_of_presentation' => $gradingData['manner_of_presentation'] ?? 0,
                'presentation_of_project_overview' => $gradingData['presentation_of_project_overview'] ?? 0,
            ];

            if ($evaluation) {
                $evaluation->update($evaluationData);
            } else {
                $evaluation = GroupRubricEvaluation::create($evaluationData);
            }

            // Update project's final grade
            $this->updateProjectFinalGrade($project);

            return $evaluation;
        });
    }

    /**
     * Update project's final grade based on all evaluations
     */
    protected function updateProjectFinalGrade(Project $project): void
    {
        $evaluations = GroupRubricEvaluation::where('project_id', $project->id)->get();

        if ($evaluations->isEmpty()) {
            return;
        }

        // Calculate average of all evaluations
        $totalScore = $evaluations->sum('total_summary_score');
        $averageScore = $totalScore / $evaluations->count();

        $project->update(['final_grade' => $averageScore]);
    }

    /**
     * Get existing evaluation for a project and panelist
     */
    public function getExistingEvaluation(Project $project, User $panelist): ?GroupRubricEvaluation
    {
        return GroupRubricEvaluation::where([
            'panel_id' => $panelist->id,
            'project_id' => $project->id,
        ])->first();
    }

    /**
     * Check if user can grade this project
     */
    public function canGradeProject(Project $project, User $user): bool
    {
        return in_array($user->id, $project->panelists ?? []);
    }

    /**
     * Get total weight for detailed criteria
     */
    public function getTotalWeight(): int
    {
        $criteria = $this->getGradingCriteria();
        return array_sum(array_column($criteria['detailed'], 'weight'));
    }

    /**
     * Get grade letter based on average score
     */
    public function getGradeLetter(float $averageScore): string
    {
        if ($averageScore >= 4.5) return 'A+';
        if ($averageScore >= 4.0) return 'A';
        if ($averageScore >= 3.5) return 'B+';
        if ($averageScore >= 3.0) return 'B';
        if ($averageScore >= 2.5) return 'C+';
        if ($averageScore >= 2.0) return 'C';
        if ($averageScore >= 1.5) return 'D';
        if ($averageScore >= 1.0) return 'F';
        return '-';
    }
}
