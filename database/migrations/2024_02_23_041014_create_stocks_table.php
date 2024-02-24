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
            $table->decimal('opening_balance', 7, 2);
            $table->decimal('receipt_qty', 7, 2);
            $table->decimal('issue_qty', 7, 2);
            $table->decimal('closing_balance', 7, 2);
            $table->uuid('material_id');
            $table->uuid('created_by');
            $table->uuid('updated_by')->nullable();
            $table->foreign('material_id')->references('material_id')->on('materials');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
