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
            $table->bigInteger('id')->unsigned();
            $table->uuid('po_id')->primary();
            $table->string('po_number', 20)->unique();
            $table->uuid('material_id')->nullable();
            $table->decimal('quantity', 10, 3);
            $table->enum('status', ['Pending', 'Completed', 'Partially Issued']);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('material_id')->references('material_id')->on('materials')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });

        DB::statement('ALTER TABLE production_orders MODIFY COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, ADD UNIQUE (id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_orders');
    }
};
