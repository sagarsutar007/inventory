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
        Schema::create('bom_records', function (Blueprint $table) {
            $table->uuid('bom_record_id')->primary();
            $table->uuid('bom_id');
            $table->uuid('material_id');
            $table->decimal('quantity', 7, 3);
            $table->timestamps();

            $table->foreign('bom_id')->references('bom_id')->on('boms')->onDelete('cascade');
            $table->foreign('material_id')->references('material_id')->on('materials')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bom_records');
    }
};
