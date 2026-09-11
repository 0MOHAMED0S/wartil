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
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('free_minutes_enabled')->default(false)->after('teacher_application_status');
            $table->decimal('free_minutes_amount', 8, 2)->default(0)->after('free_minutes_enabled');
            $table->integer('free_minutes_validity_days')->default(0)->after('free_minutes_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['free_minutes_enabled', 'free_minutes_amount', 'free_minutes_validity_days']);
        });
    }
};
