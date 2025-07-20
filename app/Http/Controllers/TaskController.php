<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

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
    public function getDocumentationTasks()
    {
        $user = Auth::user();
        $project = Project::where('group_id', $user->group_id)->first();

        if (!$project) {
            return response()->json(['error' => 'No project found for your group'], 404);
        }

        $tasks = Task::where('project_id', $project->id)->where('type', 'documentation')->get();
        // Load assigned users for each task
        foreach ($tasks as $task) {
            $task->assigned_users = $task->assignedToUsers;
        }
        return response()->json($tasks);
    }

    /**
     * Get all development tasks for a project
     */
    public function getDevelopmentTasks()
    {
        $user = Auth::user();
        $project = Project::where('group_id', $user->group_id)->first();

        if (!$project) {
            return response()->json(['error' => 'No project found for your group'], 404);
        }

        $tasks = Task::where('project_id', $project->id)->where('type', 'development')->get();
        // Load assigned users for each task
        foreach ($tasks as $task) {
            $task->assigned_users = $task->assignedToUsers;
        }
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
     * Enhanced for mobile app usage with file upload support
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Get authenticated user
            $user = Auth::user();

            // Get user's project
            $project = Project::where('group_id', $user->group_id)->first();

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'No project found for your group'
                ], 404);
            }

            // Validation rules for mobile app
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'deadline' => 'nullable|date|after:today',
                'assigned_to' => 'nullable|array',
                'assigned_to.*' => 'exists:users,id',
                'file' => 'nullable|file|max:10240', // 10MB max
                'file.*' => 'mimes:pdf,doc,docx,jpg,jpeg,png,gif,zip,rar,txt,csv,xlsx,pptx|max:10240',
                'status' => 'nullable|in:To-do,In Progress,For Review,Approved'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Handle file upload if present
            $filePath = null;
            $fileUrl = null;

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('task-files', $filename, 'public');
                $fileUrl = Storage::url($filePath);
            }

            // Create task
            $task = Task::create([
                'project_id' => $project->id,
                'title' => $request->title,
                'description' => $request->description,
                'deadline' => $request->deadline,
                'type' => 'development',
                'status' => $request->status ?? 'To-do',
                'assigned_to' => $request->assigned_to,
                'file_path' => $filePath,
                'sort' => null,
                'is_faculty_approved' => false,
            ]);

            // Load relationships for response
            $task->load('project');
            $task->assigned_users = $task->assignedToUsers;

            return response()->json([
                'success' => true,
                'message' => 'Task created successfully',
                'data' => [
                    'task' => $task,
                    'file_url' => $fileUrl,
                    'project' => $task->project->only(['id', 'title'])
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create task',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $task = Task::with('project')->find($id);

            if (!$task) {
                return response()->json([
                    'success' => false,
                    'message' => 'Task not found'
                ], 404);
            }

            // Check if user has access to this task
            $user = Auth::user();
            if ($task->project->group_id !== $user->group_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this task'
                ], 403);
            }

            $responseData = [
                'task' => $task,
                'file_url' => $task->file_path ? Storage::url($task->file_path) : null,
                'assigned_users' => $task->assignedToUsers
            ];

            return response()->json([
                'success' => true,
                'data' => $responseData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve task',
                'error' => $e->getMessage()
            ], 500);
        }
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
     * Enhanced for mobile app usage with file upload support
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $task = Task::with('project')->find($id);

            if (!$task) {
                return response()->json([
                    'success' => false,
                    'message' => 'Task not found'
                ], 404);
            }

            // Check if user has access to this task
            $user = Auth::user();
            if ($task->project->group_id !== $user->group_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this task'
                ], 403);
            }

            // Validation rules for mobile app
            $validator = Validator::make($request->all(), [
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:1000',
                'deadline' => 'nullable|date|after:today',
                'assigned_to' => 'nullable|array',
                'assigned_to.*' => 'exists:users,id',
                'file' => 'nullable|file|max:10240', // 10MB max
                'file.*' => 'mimes:pdf,doc,docx,jpg,jpeg,png,gif,zip,rar,txt,csv,xlsx,pptx|max:10240',
                'type' => 'nullable|in:development,documentation',
                'status' => 'nullable|in:To-do,In Progress,For Review,Approved'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Handle file upload if present
            $filePath = $task->file_path;
            $fileUrl = $task->file_path ? Storage::url($task->file_path) : null;

            if ($request->hasFile('file')) {
                // Delete old file if exists
                if ($task->file_path && Storage::disk('public')->exists($task->file_path)) {
                    Storage::disk('public')->delete($task->file_path);
                }

                $file = $request->file('file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('task-files', $filename, 'public');
                $fileUrl = Storage::url($filePath);
            }

            // Update task
            $task->update([
                'title' => $request->title ?? $task->title,
                'description' => $request->description ?? $task->description,
                'deadline' => $request->deadline ?? $task->deadline,
                'type' => $request->type ?? $task->type,
                'status' => $request->status ?? $task->status,
                'assigned_to' => $request->assigned_to ?? $task->assigned_to,
                'file_path' => $filePath,
            ]);

            $updatedTask = $task->fresh();
            $updatedTask->assigned_users = $updatedTask->assignedToUsers;

            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully',
                'data' => [
                    'task' => $updatedTask,
                    'file_url' => $fileUrl,
                    'project' => $task->project->only(['id', 'title'])
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update task',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $task = Task::with('project')->find($id);

            if (!$task) {
                return response()->json([
                    'success' => false,
                    'message' => 'Task not found'
                ], 404);
            }

            // Check if user has access to this task
            $user = Auth::user();
            if ($task->project->group_id !== $user->group_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this task'
                ], 403);
            }

            if ($task->type === 'documentation') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete documentation task'
                ], 400);
            }

            // Delete associated file if exists
            if ($task->file_path && Storage::disk('public')->exists($task->file_path)) {
                Storage::disk('public')->delete($task->file_path);
            }

            $task->delete();

            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete task',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
