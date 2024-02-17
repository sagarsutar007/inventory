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
        Schema::create('boms', function (Blueprint $table) {
            $table->uuid('bom_id')->primary();
            $table->uuid('material_id');
            $table->foreign('material_id')->references('material_id')->on('materials')->onDelete('cascade');
            $table->uuid('uom_id');
            $table->foreign('uom_id')->references('uom_id')->on('uom_units');
            $table->uuid('created_by');
            $table->uuid('updated_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boms');
    }
};
