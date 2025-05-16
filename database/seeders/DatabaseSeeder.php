<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(GroupSeeder::class);

        // Admin
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
        ]);

        // Faculty
        User::factory()->create([
            'name' => 'Faculty User',
            'email' => 'faculty@gmail.com',
        ]);

        // Student 1
        User::factory()->create([
            'name' => 'Student One',
            'email' => 'student1@gmail.com',
            'student_id' => '201910416',
            'course' => 'BSITWMA',
        ]);

        // Student 2
        User::factory()->create([
            'name' => 'Student Two',
            'email' => 'student2@gmail.com',
            'student_id' => '202450123',
            'course' => 'BSITAGD',
        ]);

        // Panelist 1
        User::factory()->create([
            'name' => 'Panelist One',
            'email' => 'panel1@gmail.com',
        ]);

        // Panelist 2
        User::factory()->create([
            'name' => 'Panelist Two',
            'email' => 'panel2@gmail.com',
        ]);

        // Panelist 3
        User::factory()->create([
            'name' => 'Panelist Three',
            'email' => 'panel3@gmail.com',
        ]);
    }
}
