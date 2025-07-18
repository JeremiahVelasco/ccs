<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::query()->get()->map(function ($project) {
            return [
                'id' => $project->id,
                'title' => $project->title,
                'progress' => $project->progressAttribute(),
                'status' => $project->status,
                'deadline' => $project->deadline,
                'final_grade' => $project->final_grade,
                ...$project->toArray(),
            ];
        });

        return response()->json($projects);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Project::create($request->all());

        return response()->json(['message' => 'Project created successfully']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $panelList = [];
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        foreach ($project->panelists as $panelist) {
            $user = User::find($panelist);
            if ($user) {
                $panelList[] = ['name' => $user->name];
            }
        }

        return response()->json([
            'project' => $project,
            'progress' => $project->progressAttribute(),
            'panelists' => $panelList,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        $project->update($request->all());

        return response()->json(['message' => 'Project updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        $project->delete();

        return response()->json(['message' => 'Project deleted successfully']);
    }

    public function fetchFaculty()
    {
        $faculty = User::whereHas('roles', function ($query) {
            $query->where('name', 'faculty');
        })->pluck('name', 'id');

        return response()->json($faculty);
    }

    public function assignPanelists(Request $request, string $id)
    {
        $project = Project::find($id);
        $project->panelists = $request->panelists;
        $project->save();

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        return response()->json(['message' => 'Panelists assigned successfully']);
    }

    public function projectDevelopmentTasks($groupId)
    {
        $project = Project::where('group_id', $groupId)->first();

        $tasks = Task::where('project_id', $project->id)->where('type', 'development')->get();

        return response()->json($tasks);
    }

    public function projectDocumentationTasks($groupId)
    {
        $project = Project::where('group_id', $groupId)->first();

        $tasks = Task::where('project_id', $project->id)->where('type', 'documentation')->get();

        return response()->json($tasks);
    }

    public function viewTask($taskId)
    {
        $task = Task::find($taskId);

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $response = [
            'task' => $task,
            'file_url' => $task->file_path ? Storage::url($task->file_path) : null
        ];

        return response()->json($response);
    }

    public function approveTask(string $id)
    {
        $task = Task::find($id);
        $task->status = 'Approved';
        $task->save();

        return response()->json(['message' => 'Task approved successfully']);
    }

    public function rejectTask(string $id)
    {
        $task = Task::find($id);
        $task->status = 'To-do';
        $task->save();

        return response()->json(['message' => 'Task rejected successfully']);
    }
}
