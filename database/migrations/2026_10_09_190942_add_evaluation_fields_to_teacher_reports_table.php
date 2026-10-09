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
        Schema::table('teacher_reports', function (Blueprint $table) {
            $table->string('attendance_status')->nullable()->comment('present or absent');
            $table->string('reception_level')->nullable()->comment('excellent, very_good, good, needs_follow_up');
            $table->text('notes')->nullable()->comment('Educational and sharia notes');
            $table->text('session_content')->nullable();
            
            $table->unsignedBigInteger('reportable_id')->nullable();
            $table->string('reportable_type')->nullable();
            
            $table->index(['reportable_id', 'reportable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_reports', function (Blueprint $table) {
            $table->dropColumn([
                'attendance_status',
                'reception_level',
                'notes',
                'session_content',
                'reportable_id',
                'reportable_type'
            ]);
        });
    }
};
