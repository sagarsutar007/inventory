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
        Schema::create('material_purchases', function (Blueprint $table) {
            $table->uuid('mat_pur_id')->primary();
            $table->uuid('material_id');
            $table->decimal('price', 10, 2)->nullable();
            $table->uuid('vendor_id');
            $table->timestamps();

            $table->foreign('material_id')->references('material_id')->on('materials')->onDelete('cascade');
            $table->foreign('vendor_id')->references('vendor_id')->on('vendors')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_purchases');
    }
};
