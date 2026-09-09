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
        Schema::table('packages', function (Blueprint $table) {
            $table->boolean('show_in_egypt')->default(true)->after('foreign_price');
            $table->boolean('show_in_arab')->default(true)->after('show_in_egypt');
            $table->boolean('show_in_foreign')->default(true)->after('show_in_arab');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['show_in_egypt', 'show_in_arab', 'show_in_foreign']);
        });
    }
};
