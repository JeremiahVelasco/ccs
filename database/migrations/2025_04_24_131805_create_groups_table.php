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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('logo')->nullable();
            $table->string('name');
            $table->foreignId('leader_id')->nullable()->constrained('users');
            $table->string('group_code')->unique();
            $table->string('course')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('adviser')->nullable()->constrained('users');
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->string('school_year');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
