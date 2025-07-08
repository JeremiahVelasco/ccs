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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->integer('priority')->default(2); // 1=Low, 2=Medium, 3=High, 4=Urgent
            $table->boolean('is_flexible')->default(true); // Can be rescheduled automatically
            $table->string('category')->default('other');
            $table->timestamps();

            // Add indexes for better performance
            $table->index(['start_date', 'end_date']);
            $table->index(['priority']);
            $table->index(['is_flexible', 'priority']);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
