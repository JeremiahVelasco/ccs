<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class StudentProjectController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $panelList = [];
        $project = Project::where('group_id', $user->group_id)->first();

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
            'files' => $project->files,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $project = Project::create([
            'group_id' => $user->group_id,
        ]);
    }

    public function show(string $id)
    {
        $user = auth()->user();
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

    public function update(Request $request)
    {
        $user = auth()->user();
        $project = Project::where('group_id', $user->group_id)->update([
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'panelists' => $request->panelists,
            'status' => $request->status,
        ]);

        return response()->json($project);
    }

    public function destroy()
    {
        $user = auth()->user();
        $project = Project::where('group_id', $user->group_id)->delete();
    }
}
