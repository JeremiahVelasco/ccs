<?php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use App\Models\EvaluationScore;
use App\Models\Rubric;
use App\Models\Group;
use App\Models\User;
use App\Services\EvaluationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EvaluationController extends Controller
{
    protected $evaluationService;

    public function __construct(EvaluationService $evaluationService)
    {
        $this->evaluationService = $evaluationService;
    }

    public function index(Request $request)
    {
        $evaluations = Evaluation::with(['rubric', 'panelist', 'evaluable', 'scores.criteria'])
            ->when($request->panelist_id, function ($query, $panelistId) {
                return $query->where('panelist_id', $panelistId);
            })
            ->when($request->rubric_type, function ($query, $type) {
                return $query->whereHas('rubric', function ($q) use ($type) {
                    $q->where('type', $type);
                });
            })
            ->get();

        return response()->json($evaluations);
    }

    public function store(Request $request)
    {
        $request->validate([
            'rubric_id' => 'required|exists:rubrics,id',
            'panelist_id' => 'required|exists:users,id',
            'evaluable_type' => 'required|in:App\\Models\\Group,App\\Models\\User',
            'evaluable_id' => 'required|integer',
            'scores' => 'required|array',
            'scores.*.rubric_criteria_id' => 'required|exists:rubric_criteria,id',
            'scores.*.score' => 'required|integer|min:1|max:5',
            'scores.*.comments' => 'nullable|string',
            'comments' => 'nullable|string'
        ]);

        $evaluation = $this->evaluationService->createEvaluation($request->all());

        return response()->json($evaluation->load(['rubric', 'panelist', 'evaluable', 'scores.criteria']), 201);
    }

    public function show($id)
    {
        $evaluation = Evaluation::with(['rubric.sections.criteria.scaleLevels', 'panelist', 'evaluable', 'scores.criteria'])
            ->findOrFail($id);

        return response()->json($evaluation);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'scores' => 'required|array',
            'scores.*.rubric_criteria_id' => 'required|exists:rubric_criteria,id',
            'scores.*.score' => 'required|integer|min:1|max:5',
            'scores.*.comments' => 'nullable|string',
            'comments' => 'nullable|string'
        ]);

        $evaluation = Evaluation::findOrFail($id);
        $updatedEvaluation = $this->evaluationService->updateEvaluation($evaluation, $request->all());

        return response()->json($updatedEvaluation->load(['rubric', 'panelist', 'evaluable', 'scores.criteria']));
    }

    public function getForPanelist($panelistId)
    {
        $panelist = User::findOrFail($panelistId);

        $groupEvaluations = $this->evaluationService->getPendingEvaluations($panelistId, 'group');
        $individualEvaluations = $this->evaluationService->getPendingEvaluations($panelistId, 'individual');

        return response()->json([
            'panelist' => $panelist,
            'group_evaluations' => $groupEvaluations,
            'individual_evaluations' => $individualEvaluations
        ]);
    }

    public function createEvaluationForm(Request $request)
    {
        $request->validate([
            'rubric_id' => 'required|exists:rubrics,id',
            'panelist_id' => 'required|exists:users,id',
            'evaluable_type' => 'required|in:group,individual',
            'evaluable_id' => 'required|integer'
        ]);

        $rubric = Rubric::with(['sections.criteria.scaleLevels'])->findOrFail($request->rubric_id);
        $panelist = User::findOrFail($request->panelist_id);

        $evaluableClass = $request->evaluable_type === 'group' ? Group::class : User::class;
        $evaluable = $evaluableClass::findOrFail($request->evaluable_id);

        // Check if evaluation already exists
        $existingEvaluation = Evaluation::where([
            'rubric_id' => $request->rubric_id,
            'panelist_id' => $request->panelist_id,
            'evaluable_type' => $evaluableClass,
            'evaluable_id' => $request->evaluable_id
        ])->first();

        return response()->json([
            'rubric' => $rubric,
            'panelist' => $panelist,
            'evaluable' => $evaluable,
            'existing_evaluation' => $existingEvaluation ?
                $existingEvaluation->load(['scores.criteria']) : null
        ]);
    }

    public function getEvaluationSummary($evaluableType, $evaluableId)
    {
        $evaluableClass = $evaluableType === 'group' ? Group::class : User::class;
        $evaluable = $evaluableClass::with($evaluableType === 'group' ? 'students' : [])->findOrFail($evaluableId);

        $evaluations = Evaluation::with(['panelist', 'rubric', 'scores.criteria'])
            ->where('evaluable_type', $evaluableClass)
            ->where('evaluable_id', $evaluableId)
            ->where('is_completed', true)
            ->get();

        $summary = $this->evaluationService->generateEvaluationSummary($evaluations);

        return response()->json([
            'evaluable' => $evaluable,
            'evaluations' => $evaluations,
            'summary' => $summary
        ]);
    }
}
