<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared('
            CREATE FUNCTION get_reserved_qty(material_id_key CHAR(36)) RETURNS DECIMAL(10, 5) DETERMINISTIC
            BEGIN
                DECLARE total_reserved_qty DECIMAL(10, 5);
                SELECT SUM(
                    CASE 
                        WHEN po.status = "Pending" THEN po.quantity * br.quantity
                        WHEN po.status != "Pending" AND ((po.quantity * br.quantity) - IFNULL(pom.quantity, 0)) != 0 
                            THEN ((po.quantity * br.quantity) - IFNULL(pom.quantity, 0))
                        ELSE 0 
                    END
                ) INTO total_reserved_qty 
                FROM production_orders po 
                INNER JOIN boms b ON b.material_id = po.material_id 
                INNER JOIN bom_records br ON b.bom_id = br.bom_id 
                LEFT OUTER JOIN prod_orders_materials pom ON po.po_id = pom.po_id AND pom.material_id = br.material_id 
                WHERE po.status != "Completed" 
                AND br.material_id = material_id_key;
                RETURN total_reserved_qty;
            END;
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP FUNCTION IF EXISTS get_reserved_qty');
    }
};
