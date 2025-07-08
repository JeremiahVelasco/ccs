<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->text(),
            'title' => fake()->text(),
            'logo' => fake()->text(),
            'group_id' => fake()->text(),
            'description' => $this->faker->paragraph(),
            'panelists' => $this->faker->json(),
            'status' => fake()->text(),
            'progress' => fake()->text(),
            'final_grade' => fake()->text(),
            'awards' => $this->faker->json(),
            'completion_probability' => fake()->text(),
            'last_prediction_at' => $this->faker->dateTime(),
            'deadline' => $this->faker->dateTime(),
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
        ];
    }
}
