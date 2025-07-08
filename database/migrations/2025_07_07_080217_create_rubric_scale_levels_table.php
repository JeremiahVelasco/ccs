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
        Schema::create('rubric_scale_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rubric_criteria_id')->constrained('rubric_criteria')->onDelete('cascade');
            $table->decimal('points', 5, 2);
            $table->string('level_name');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rubric_scale_levels');
    }
};
