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
            $table->renameColumn('price', 'egypt_price');
            $table->decimal('arab_price', 8, 2)->default(0)->after('egypt_price');
            $table->decimal('foreign_price', 8, 2)->default(0)->after('arab_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['arab_price', 'foreign_price']);
            $table->renameColumn('egypt_price', 'price');
        });
    }
};
