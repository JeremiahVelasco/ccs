<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class PanelistController extends Controller
{
    public function index()
    {
        $panelists = User::where('is_active', true)->get();
        return response()->json($panelists);
    }

    public function show($id)
    {
        $panelist = User::with(['evaluations.rubric', 'evaluations.evaluable'])
            ->findOrFail($id);

        return response()->json($panelist);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:panelists,email',
            'title' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255'
        ]);

        $panelist = User::create($request->all());
        return response()->json($panelist, 201);
    }

    public function update(Request $request, $id)
    {
        $panelist = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:panelists,email,' . $id,
            'title' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255'
        ]);

        $panelist->update($request->all());
        return response()->json($panelist);
    }

    public function getDashboard($id)
    {
        $panelist = User::findOrFail($id);

        $completedEvaluations = $panelist->evaluations()->where('is_completed', true)->count();
        $pendingEvaluations = $panelist->evaluations()->where('is_completed', false)->count();

        $recentEvaluations = $panelist->evaluations()
            ->with(['rubric', 'evaluable'])
            ->where('is_completed', true)
            ->orderBy('completed_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'panelist' => $panelist,
            'stats' => [
                'completed_evaluations' => $completedEvaluations,
                'pending_evaluations' => $pendingEvaluations,
                'total_evaluations' => $completedEvaluations + $pendingEvaluations
            ],
            'recent_evaluations' => $recentEvaluations
        ]);
    }
}
