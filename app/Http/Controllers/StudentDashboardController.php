<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Check if user has a project
        if (!$user->group || !$user->group->project) {
            return response()->json([
                'error' => 'No project found',
                'message' => 'You need to be assigned to a project to view the dashboard.'
            ], 404);
        }

        $projectProgress = $this->getProjectProgress();
        $taskStats = $this->getTaskStats();
        $progressOverview = $this->getProgressOverview();

        return response()->json([
            'projectProgress' => $projectProgress,
            'taskStats' => $taskStats,
            'progressOverview' => $progressOverview,
        ]);
    }

    protected function getProjectProgress()
    {
        $user = Auth::user();
        $project = $user->group->project;

        return $project->progress;
    }

    protected function getTaskStats()
    {
        $user = Auth::user();
        $project = $user->group->project;

        // Get documentation stats in one optimized query
        $docStats = $project->documentationTasks()
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "Approved" THEN 1 ELSE 0 END) as approved
            ')
            ->first();

        // Get development stats in one optimized query
        $devStats = $project->developmentTasks()
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "Approved" THEN 1 ELSE 0 END) as approved
            ')
            ->first();

        // Calculate progress with division by zero protection
        $documentationProgress = $docStats->total > 0
            ? ($docStats->approved / $docStats->total) * 100
            : 0;

        $developmentProgress = $devStats->total > 0
            ? ($devStats->approved / $devStats->total) * 100
            : 0;

        return [
            'documentationProgress' => round($documentationProgress, 2),
            'developmentProgress' => round($developmentProgress, 2),
        ];
    }

    protected function getProgressOverview()
    {
        $user = Auth::user();
        $project = $user->group->project;

        $tasks = $project->tasks()->get();
        $todoTasks = $tasks->where('status', 'To-do')->count();
        $inProgressTasks = $tasks->where('status', 'In Progress')->count();
        $forReviewTasks = $tasks->where('status', 'For Review')->count();
        $approvedTasks = $tasks->whereIn('status', ['Approved', 'Done'])->count();

        return [
            'todoTasks' => $todoTasks,
            'inProgressTasks' => $inProgressTasks,
            'forReviewTasks' => $forReviewTasks,
            'approvedTasks' => $approvedTasks,
        ];
    }
}
