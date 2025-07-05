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
        Schema::table('projects', function (Blueprint $table) {
            $table->date('deadline')->nullable()->after('description');
            $table->json('prediction_config')->nullable()->after('last_prediction_at');
            $table->integer('prediction_version')->default(1)->after('prediction_config');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['deadline', 'prediction_config', 'prediction_version']);
        });
    }
};
