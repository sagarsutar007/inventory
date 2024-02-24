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
        Schema::create('warehouse', function (Blueprint $table) {
            $table->uuid('warehouse_id')->primary()->unique();
            $table->string('transaction_id', 20)->unique();
            $table->string('popn', 20)->unique();
            $table->enum('type', ['issue', 'receive', 'none'])->default('none')->nullable();
            // $table->uuid('stock_id')->nullable();
            $table->uuid('vendor_id')->nullable();
            $table->date('date')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            // $table->foreign('stock_id')->references('stock_id')->on('stocks')->onDelete('set null');
            $table->foreign('vendor_id')->references('vendor_id')->on('vendors')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse');
    }
};
