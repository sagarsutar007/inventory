<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prod_orders_materials', function (Blueprint $table) {
            $table->uuid('pom_id')->primary();
            $table->uuid('po_id');
            $table->uuid('material_id');
            $table->decimal('quantity', 10, 3);
            $table->timestamps();

            $table->foreign('po_id')->references('po_id')->on('production_orders');
            $table->foreign('material_id')->references('material_id')->on('materials');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prod_orders_materials');
    }
};