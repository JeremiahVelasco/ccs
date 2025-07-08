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
        Schema::create('grade_summary', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('panel_id')->constrained('users');
            $table->integer('group_presentation_score');
            $table->integer('member_1_score');
            $table->integer('member_2_score');
            $table->integer('member_3_score');
            $table->integer('member_4_score');
            $table->integer('member_5_score');
            $table->integer('member_6_score');
            $table->integer('member_7_score');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_summary');
    }
};
