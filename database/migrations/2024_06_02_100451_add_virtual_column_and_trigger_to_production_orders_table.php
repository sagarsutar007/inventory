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
        Schema::table('production_orders', function (Blueprint $table) {
            // $table->string('po_number', 20)->virtual()->nullable();
        });

        DB::unprepared('
            CREATE TRIGGER reset_yearly_id_po BEFORE INSERT ON production_orders
            FOR EACH ROW
            BEGIN
                DECLARE max_id BIGINT; 
                IF NEW.created_at IS NOT NULL THEN
                    SELECT COALESCE(MAX(year_max_id), 0) INTO max_id
                    FROM production_orders
                    WHERE YEAR(created_at) = YEAR(NEW.created_at) - 1;
                    SET NEW.year_max_id = max_id;
                END IF;
            END;
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_orders', function (Blueprint $table) {
            // $table->dropColumn('po_number');
        });

        DB::unprepared('DROP TRIGGER IF EXISTS reset_yearly_id_po');
    }
};
