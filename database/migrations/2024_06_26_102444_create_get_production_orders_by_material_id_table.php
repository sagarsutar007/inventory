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
            CREATE PROCEDURE get_production_orders_by_material_id(IN material_id_key CHAR(36))
            BEGIN
                SELECT
                    po.po_number,
                    m.part_code,
                    po.quantity,
                    po.status,
                    br.quantity AS bom_qty,
                    po.quantity * br.quantity AS qpa,
                    pom.quantity AS qty_issued,
                    CASE
                        WHEN po.status = "Pending" THEN po.quantity * br.quantity
                        WHEN po.status != "Pending" AND ((po.quantity * br.quantity) - IFNULL(pom.quantity, 0)) != 0
                            THEN ((po.quantity * br.quantity) - IFNULL(pom.quantity, 0))
                        ELSE 0
                    END AS reserved_qty
                FROM
                    production_orders po
                INNER JOIN materials m ON po.material_id = m.material_id
                INNER JOIN boms b ON b.material_id = po.material_id
                INNER JOIN bom_records br ON b.bom_id = br.bom_id
                LEFT OUTER JOIN prod_orders_materials pom ON po.po_id = pom.po_id AND pom.material_id = br.material_id
                WHERE
                    po.status != "Completed"
                    AND br.material_id COLLATE utf8mb4_unicode_ci = material_id_key COLLATE utf8mb4_unicode_ci;
            END;
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS get_production_orders_by_material_id');
    }
};
