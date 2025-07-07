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
        Schema::create('prediction_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->decimal('completion_probability', 5, 4);
            $table->integer('completion_percentage');
            $table->string('risk_level');

            // Store the feature values used for prediction
            $table->json('features');
            $table->json('feature_descriptions');
            $table->json('recommendations')->nullable();

            // Metadata about the prediction
            $table->string('prediction_method')->default('bayesian_network');
            $table->string('model_version')->default('1.0');
            $table->decimal('model_confidence', 5, 4)->nullable();

            // Performance metrics
            $table->integer('execution_time_ms')->nullable();
            $table->boolean('used_cache')->default(false);
            $table->json('model_info')->nullable();

            // Additional context
            $table->json('project_metadata')->nullable(); // snapshot of project state
            $table->string('triggered_by')->nullable(); // 'auto', 'manual', 'scheduled'
            $table->foreignId('user_id')->nullable()->constrained(); // who triggered the prediction

            $table->timestamps();

            // Indexes for performance
            $table->index(['project_id', 'created_at']);
            $table->index(['risk_level', 'created_at']);
            $table->index(['completion_probability', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prediction_history');
    }
};
