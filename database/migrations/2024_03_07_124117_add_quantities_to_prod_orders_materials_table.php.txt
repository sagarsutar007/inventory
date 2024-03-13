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
        Schema::table('prod_orders_materials', function (Blueprint $table) {
            $table->decimal('bom_qty', 10, 3)->after('quantity')->nullable();
            $table->decimal('qty_needed', 10, 3)->after('bom_qty')->nullable();
            $table->decimal('issued_qty', 10, 3)->after('qty_needed')->default(0);
            $table->decimal('stock_in_hand', 10, 3)->after('issued_qty')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prod_orders_materials', function (Blueprint $table) {
            //
        });
    }
};
