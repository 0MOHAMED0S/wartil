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
        Schema::create('teacher_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('egypt_rate', 8, 2)->default(0);
            $table->decimal('arab_rate', 8, 2)->default(0);
            $table->decimal('foreign_rate', 8, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_categories');
    }
};
