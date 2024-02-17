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
        Schema::create('materials', function (Blueprint $table) {
            $table->uuid('material_id')->primary();
            $table->string('part_code', 20)->unique()->nullable(false);
            $table->string('description')->nullable(false);
            $table->text('additional_notes')->nullable();
            $table->uuid('uom_id');
            $table->enum('type', ['raw','semi-finished','finished'])->default('raw');
            $table->decimal('price', 7, 3)->nullable();
            $table->uuid('commodity_id');
            $table->uuid('category_id');
            $table->timestamps();
            $table->foreign('commodity_id')->references('commodity_id')->on('commodities');
            $table->foreign('category_id')->references('category_id')->on('categories');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
