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
        Schema::table('materials', function (Blueprint $table) {
            $table->decimal('avg_price', 10,2)->nullable();
            $table->decimal('min_price', 10,2)->nullable();
            $table->decimal('max_price', 10,2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn('avg_price');
            $table->dropColumn('min_price');
            $table->dropColumn('max_price');
        });
    }
};
