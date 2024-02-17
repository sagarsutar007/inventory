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
        Schema::table('user_logs', function (Blueprint $table) {
            $table->string('ip_address', 15)->nullable();
            $table->string('mac_address', 100)->nullable();
            $table->string('device_type', 60)->nullable();
            $table->text('client', 60)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_logs', function (Blueprint $table) {
            $table->dropColumn('ip_address');
            $table->dropColumn('mac_address');
            $table->dropColumn('device_type');
            $table->dropColumn('client');
        });
    }
};
