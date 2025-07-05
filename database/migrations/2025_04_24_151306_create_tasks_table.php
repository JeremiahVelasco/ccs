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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->json('assigned_to')->nullable();
            $table->text('description')->nullable();
            $table->date('deadline')->nullable();
            $table->string('type')->nullable();
            $table->date('date_accomplished')->nullable();
            $table->string('status');
            $table->string('file_path')->nullable();
            $table->integer('sort')->nullable();
            $table->boolean('is_faculty_approved')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
