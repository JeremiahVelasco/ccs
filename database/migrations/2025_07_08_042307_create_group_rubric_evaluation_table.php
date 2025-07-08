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
        Schema::create('group_rubric_evaluation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('panel_id')->constrained('users');
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->integer('documentation_score');
            $table->integer('prototype_score');
            $table->integer('presentation_score');
            $table->integer('total_summary_score');
            $table->integer('presentation_of_results');
            $table->integer('summary_of_findings');
            $table->integer('conclusion');
            $table->integer('recommendation');
            $table->integer('content');
            $table->integer('project_output');
            $table->integer('relevance_to_specialization');
            $table->integer('project_demonstration');
            $table->integer('consistency');
            $table->integer('materials');
            $table->integer('manner_of_presentation');
            $table->integer('presentation_of_project_overview');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_rubric_evaluation');
    }
};
