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
        Schema::create('material_attachments', function (Blueprint $table) {
            $table->uuid('mat_doc_id');
            $table->text('path')->nullable(false);
            $table->enum('type', ['pdf', 'doc', 'image'])->nullable(); 
            $table->uuid('material_id');
            $table->timestamps();

            $table->foreign('material_id')->references('material_id')->on('materials')->onDelete('cascade');
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_attachments');
    }
};
