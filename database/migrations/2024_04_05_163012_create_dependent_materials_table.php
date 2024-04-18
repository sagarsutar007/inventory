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
        Schema::create('dependent_materials', function (Blueprint $table) {
            $table->uuid('dm_id')->primary();
            $table->string('description', 120);
            $table->enum('frequency', ['Daily', 'Weekly', 'Bi-weekly', 'Monthly', 'Bi-monthly', 'Quarterly', 'Half-yearly', 'Yearly', 'Other'])->default('Monthly');
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dependent_materials');
    }
};
