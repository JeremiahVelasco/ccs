<?php

namespace App\Services;

use App\Models\Evaluation;
use App\Models\EvaluationScore;
use App\Models\Rubric;
use App\Models\Group;
use App\Models\User;
use App\Models\RubricCriteria;
use Illuminate\Support\Facades\DB;

class EvaluationService
{
    public function createEvaluation(array $data): Evaluation
    {
        return DB::transaction(function () use ($data) {
            $evaluableClass = $data['evaluable_type'];

            // Create or update evaluation
            $evaluation = Evaluation::updateOrCreate(
                [
                    'rubric_id' => $data['rubric_id'],
                    'panelist_id' => $data['panelist_id'],
                    'evaluable_type' => $evaluableClass,
                    'evaluable_id' => $data['evaluable_id']
                ],
                [
                    'comments' => $data['comments'] ?? null,
                    'is_completed' => false
                ]
            );

            // Delete existing scores
            $evaluation->scores()->delete();

            // Create new scores
            foreach ($data['scores'] as $scoreData) {
                $criteria = RubricCriteria::findOrFail($scoreData['rubric_criteria_id']);

                EvaluationScore::create([
                    'evaluation_id' => $evaluation->id,
                    'rubric_criteria_id' => $scoreData['rubric_criteria_id'],
                    'score' => $scoreData['score'],
                    'weighted_score' => $scoreData['score'] * $criteria->weight,
                    'comments' => $scoreData['comments'] ?? null
                ]);
            }

            // Mark as completed and calculate total
            $evaluation->markAsCompleted();

            return $evaluation;
        });
    }

    public function updateEvaluation(Evaluation $evaluation, array $data): Evaluation
    {
        return DB::transaction(function () use ($evaluation, $data) {
            $evaluation->update([
                'comments' => $data['comments'] ?? $evaluation->comments,
                'is_completed' => false
            ]);

            // Delete existing scores
            $evaluation->scores()->delete();

            // Create new scores
            foreach ($data['scores'] as $scoreData) {
                $criteria = RubricCriteria::findOrFail($scoreData['rubric_criteria_id']);

                EvaluationScore::create([
                    'evaluation_id' => $evaluation->id,
                    'rubric_criteria_id' => $scoreData['rubric_criteria_id'],
                    'score' => $scoreData['score'],
                    'weighted_score' => $scoreData['score'] * $criteria->weight,
                    'comments' => $scoreData['comments'] ?? null
                ]);
            }

            // Mark as completed and calculate total
            $evaluation->markAsCompleted();

            return $evaluation;
        });
    }

    public function getPendingEvaluations($panelistId, $type)
    {
        $rubrics = Rubric::where('type', $type)->where('is_active', true)->get();
        $pendingEvaluations = [];

        foreach ($rubrics as $rubric) {
            if ($type === 'group') {
                $groups = Group::with('students')->get();
                foreach ($groups as $group) {
                    $existingEvaluation = Evaluation::where([
                        'rubric_id' => $rubric->id,
                        'panelist_id' => $panelistId,
                        'evaluable_type' => Group::class,
                        'evaluable_id' => $group->id
                    ])->first();

                    $pendingEvaluations[] = [
                        'rubric' => $rubric,
                        'evaluable' => $group,
                        'existing_evaluation' => $existingEvaluation,
                        'is_completed' => $existingEvaluation ? $existingEvaluation->is_completed : false
                    ];
                }
            } else {
                $students = User::get();
                foreach ($students as $student) {
                    $existingEvaluation = Evaluation::where([
                        'rubric_id' => $rubric->id,
                        'panelist_id' => $panelistId,
                        'evaluable_type' => User::class,
                        'evaluable_id' => $student->id
                    ])->first();

                    $pendingEvaluations[] = [
                        'rubric' => $rubric,
                        'evaluable' => $student,
                        'existing_evaluation' => $existingEvaluation,
                        'is_completed' => $existingEvaluation ? $existingEvaluation->is_completed : false
                    ];
                }
            }
        }

        return $pendingEvaluations;
    }

    public function generateEvaluationSummary($evaluations)
    {
        if ($evaluations->isEmpty()) {
            return null;
        }

        $summary = [
            'total_evaluations' => $evaluations->count(),
            'average_score' => $evaluations->avg('total_score'),
            'highest_score' => $evaluations->max('total_score'),
            'lowest_score' => $evaluations->min('total_score'),
            'criteria_breakdown' => []
        ];

        // Group scores by criteria
        $criteriaScores = [];
        foreach ($evaluations as $evaluation) {
            foreach ($evaluation->scores as $score) {
                $criteriaId = $score->rubric_criteria_id;
                if (!isset($criteriaScores[$criteriaId])) {
                    $criteriaScores[$criteriaId] = [
                        'criteria' => $score->criteria,
                        'scores' => []
                    ];
                }
                $criteriaScores[$criteriaId]['scores'][] = $score->score;
            }
        }

        // Calculate averages for each criteria
        foreach ($criteriaScores as $criteriaId => $data) {
            $summary['criteria_breakdown'][] = [
                'criteria' => $data['criteria'],
                'average_score' => collect($data['scores'])->avg(),
                'total_scores' => $data['scores']
            ];
        }

        return $summary;
    }
}
