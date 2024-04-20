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
        Schema::create('warehouse_records', function (Blueprint $table) {
            $table->uuid('warehouse_record_id')->primary();
            $table->uuid('warehouse_id');
            $table->uuid('material_id');
            $table->enum('warehouse_type', ['issued', 'received', 'reversed']);
            $table->string('quantity', 20)->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->foreign('warehouse_id')->references('warehouse_id')->on('warehouse');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('material_id')->references('material_id')->on('materials');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_records');
    }
};
