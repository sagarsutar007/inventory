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
        Schema::create('stocks', function (Blueprint $table) {
            $table->uuid('stock_id')->primary()->unique();
            $table->decimal('opening_balance', 10, 3);
            $table->decimal('receipt_qty', 10, 3);
            $table->decimal('issue_qty', 10, 3);
            $table->uuid('material_id')->nullable();
            $table->uuid('created_by');
            $table->uuid('updated_by')->nullable();
            $table->foreign('material_id')->references('material_id')->on('materials')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE stocks ADD COLUMN closing_balance DEC(10,2) GENERATED ALWAYS AS (opening_balance+receipt_qty-issue_qty) STORED');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
