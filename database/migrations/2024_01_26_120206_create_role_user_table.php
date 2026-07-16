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
        Schema::create('role_user', function (Blueprint $table) {
            // MySQL does not permit UUID() as a column default.
            $table->uuid('id')->primary();
            $table->uuid('role_id');
            $table->uuid('user_id');
            $table->foreign('role_id')->references('id')->on('roles');
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
        });

        // Preserve UUID generation for direct pivot operations such as sync(),
        // while the UserRole model generates IDs itself through HasUuids.
        DB::unprepared("CREATE TRIGGER role_user_generate_uuid BEFORE INSERT ON role_user FOR EACH ROW SET NEW.id = IF(NEW.id IS NULL OR NEW.id = '', UUID(), NEW.id)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS role_user_generate_uuid');
        Schema::dropIfExists('role_user');
    }
};
