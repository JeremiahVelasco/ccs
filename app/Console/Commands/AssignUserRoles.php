<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class AssignUserRoles extends Command
{
    protected $signature = 'users:assign-roles';
    protected $description = 'Assign roles to existing users based on email patterns';

    public function handle()
    {
        $users = User::whereDoesntHave('roles')->get();

        if ($users->isEmpty()) {
            $this->info('No users without roles found.');
            return;
        }

        foreach ($users as $user) {
            $role = $this->determineRole($user);

            if ($role) {
                $user->assignRole($role);
                $this->info("Assigned role '{$role}' to user: {$user->name} ({$user->email})");
            } else {
                $this->warn("Could not determine role for user: {$user->name} ({$user->email})");
            }
        }

        $this->info('Role assignment completed!');
    }

    private function determineRole(User $user): ?string
    {
        // Check if user has student_id - likely a student
        if ($user->student_id) {
            return 'student';
        }

        // Check email patterns (adjust these patterns based on your needs)
        $email = $user->email;

        if (str_contains($email, 'student')) {
            return 'student';
        }

        if (str_contains($email, 'faculty') || str_contains($email, 'professor')) {
            return 'faculty';
        }

        if (str_contains($email, 'admin') || str_contains($email, 'super')) {
            return 'super_admin';
        }

        // Default to student for unknown cases
        return 'student';
    }
}
