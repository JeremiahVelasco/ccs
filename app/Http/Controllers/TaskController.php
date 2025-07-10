<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Get all documentation tasks for a project
     */
    public function getDocumentationTasks(string $id)
    {
        $tasks = Task::where('project_id', $id)->where('type', 'documentation')->get();
        return response()->json($tasks);
    }

    /**
     * Get all development tasks for a project
     */
    public function getDevelopmentTasks(string $id)
    {
        $tasks = Task::where('project_id', $id)->where('type', 'development')->get();
        return response()->json($tasks);
    }

    /**
     * Filter tasks by status
     */
    public function filterTasks(string $id, string $type, string $status)
    {
        $tasks = Task::where('project_id', $id)
            ->where('type', $type)
            ->where('status', $status)
            ->get();

        return response()->json($tasks);
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
        $task = Task::create([
            'project_id' => $request->project_id,
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'type' => 'development',
            'status' => 'To-do',
            'assigned_to' => $request->assigned_to,
            'file_path' => null,
            'sort' => null,
            'is_faculty_approved' => false,
        ]);
        return response()->json($task);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $task = Task::find($id);

        return response()->json($task);
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
        $task = Task::find($id);
        $task->update($request->all());

        return response()->json($task);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $task = Task::find($id);
        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }
}
