<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('individual_rubric_evaluation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('panel_id')->constrained('users');
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users');
            $table->integer('subject_mastery');
            $table->integer('ability_to_answer_questions');
            $table->integer('delivery');
            $table->integer('verbal_and_nonverbal_ability');
            $table->integer('grooming');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('individual_rubric_evaluation');
    }
};
