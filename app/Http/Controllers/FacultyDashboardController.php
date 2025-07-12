<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Project;
use Illuminate\Http\Request;

class FacultyDashboardController extends Controller
{
    public function index()
    {
        $advisedGroups = $this->getAdvisedGroups();
        $panelistAssignments = $this->getPanelistAssignments();
        $activeProjects = $this->getActiveProjects();

        $advisedGroupsCount = $advisedGroups->count();
        $panelistAssignmentsCount = $panelistAssignments->count();
        $activeProjectsCount = $activeProjects->count();

        return response()->json([
            'advisedGroups' => $advisedGroups,
            'panelistAssignments' => $panelistAssignments,
            'activeProjects' => $activeProjects,
            'advisedGroupsCount' => $advisedGroupsCount,
            'panelistAssignmentsCount' => $panelistAssignmentsCount,
            'activeProjectsCount' => $activeProjectsCount,
        ]);
    }

    protected function getAdvisedGroups()
    {
        $user = auth()->user();

        $groups = Group::where('adviser', $user->id)->get();

        return $groups;
    }

    protected function getPanelistAssignments()
    {
        $user = auth()->user();

        $projects = Project::where('panelists', $user->id)->get();

        return $projects;
    }

    protected function getActiveProjects()
    {
        $projects = Project::where('status', '!=', 'Done')->get();

        return $projects;
    }
}
