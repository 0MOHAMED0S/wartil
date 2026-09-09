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
        Schema::table('call_sessions', function (Blueprint $table) {
            $table->string('student_region')->nullable()->after('duration_minutes');
            $table->decimal('teacher_earnings', 10, 2)->default(0)->after('student_region');
        });

        Schema::table('slot_bookings', function (Blueprint $table) {
            $table->string('student_region')->nullable()->after('actual_duration');
            $table->decimal('teacher_earnings', 10, 2)->default(0)->after('student_region');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('call_sessions', function (Blueprint $table) {
            $table->dropColumn(['student_region', 'teacher_earnings']);
        });

        Schema::table('slot_bookings', function (Blueprint $table) {
            $table->dropColumn(['student_region', 'teacher_earnings']);
        });
    }
};
