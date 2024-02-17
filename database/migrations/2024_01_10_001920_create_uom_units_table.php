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
        Schema::create('uom_units', function (Blueprint $table) {
            $table->uuid('uom_id')->primary();
            $table->string('uom_text', 20)->unique()->nullable(false);
            $table->string('uom_shortcode', 5)->unique()->nullable(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uom_units');
    }
};
