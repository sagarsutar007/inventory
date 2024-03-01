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
        Schema::create('production_orders', function (Blueprint $table) {
            $table->uuid('po_id')->primary();
            $table->bigInteger('po_number')->unique();
            $table->uuid('material_id')->nullable();
            $table->decimal('quantity', 10, 3);
            $table->enum('status', ['Issued', 'Pending', 'Shortage', 'Draft', 'Approved']);
            $table->timestamps();

            $table->foreign('material_id')->references('material_id')->on('materials')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_orders');
    }
};
