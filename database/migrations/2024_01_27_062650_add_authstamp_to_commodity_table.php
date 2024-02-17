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
        Schema::table('commodities', function (Blueprint $table) {
            $table->foreignUuid('created_by')->after('commodity_number')->nullable();
            $table->foreignUuid('updated_by')->after('created_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commodity', function (Blueprint $table) {
            // $table->dropColumn('created_by');
            // $table->dropColumn('updated_by');
        });
    }
};
