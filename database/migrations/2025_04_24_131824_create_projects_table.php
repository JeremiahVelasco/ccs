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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('logo')->nullable();
            $table->foreignId('group_id')->nullable()->constrained()->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->json('panelists')->nullable();
            $table->string('status')->default('In Progress');
            $table->string('progress')->nullable();
            $table->string('final_grade')->nullable();
            $table->json('awards')->nullable();
            $table->decimal('completion_probability', 5, 4)->nullable();
            $table->timestamp('last_prediction_at')->nullable();
            $table->integer('prediction_version')->default(0);
            $table->dateTime('deadline')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
