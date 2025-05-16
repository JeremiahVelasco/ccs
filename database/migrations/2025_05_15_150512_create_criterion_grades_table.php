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
        Schema::create('criterion_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_grade_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rubric_criterion_id')->constrained()->cascadeOnDelete();
            $table->integer('score');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('criterion_grades');
    }
};
