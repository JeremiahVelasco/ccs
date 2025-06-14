<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Project;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Allow if user has view_any_project permission
        if ($user->can('view_any_project')) {
            return true;
        }

        // Allow students to view repository projects list
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        // Allow viewing if user has view_project permission
        if ($user->can('view_project')) {
            return true;
        }

        // Allow students to view repository projects (status = Done)
        if ($project->status === 'Done') {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_project');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        return $user->can('update_project');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->can('delete_project');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_project');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return $user->can('force_delete_project');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_project');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Project $project): bool
    {
        return $user->can('restore_project');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_project');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Project $project): bool
    {
        return $user->can('replicate_project');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_project');
    }
}
