SELECT
    po.po_number, po.quantity, br.quantity, po.status
FROM
    production_orders po
INNER JOIN boms b ON
    b.material_id = po.material_id
INNER JOIN bom_records br ON
    b.bom_id = br.bom_id AND br.material_id =(
    SELECT
        material_id
    FROM
        materials
    WHERE
        part_code = "1101200007"
)
WHERE
    po.status != 'Completed'



SELECT
    po.po_number,
    (SELECT part_code FROM materials WHERE material_id = po.material_id) AS part_code,
    po.quantity,
    br.quantity,
    po.status,
    po.record_date,
    po.quantity * br.quantity AS qpa,
    pom.quantity,
    CASE 
        WHEN po.status = 'Pending' THEN po.quantity * br.quantity 
        WHEN po.status != 'Pending' AND ((po.quantity * br.quantity) - IFNULL(pom.quantity, 0)) != 0 THEN ((po.quantity * br.quantity) - IFNULL(pom.quantity, 0)) 
        ELSE 0 
    END AS reserved_qty,
    pom.status
FROM
    production_orders po
INNER JOIN boms b ON
    b.material_id = po.material_id
INNER JOIN bom_records br ON
    b.bom_id = br.bom_id 
    AND br.material_id = (
        SELECT
            material_id
        FROM
            materials
        WHERE
            part_code = '1301400042'
    )
LEFT OUTER JOIN prod_orders_materials pom ON
    po.po_id = pom.po_id 
    AND pom.material_id = br.material_id
WHERE
    po.status != 'Completed';


SELECT
    SUM(
        CASE WHEN po.status = 'Pending' THEN po.quantity * br.quantity WHEN po.status != 'Pending' AND(
            (po.quantity * br.quantity) - IFNULL(pom.quantity, 0)
        ) != 0 THEN(
            (po.quantity * br.quantity) - IFNULL(pom.quantity, 0)
        ) ELSE 0
    END
) AS total_reserved_qty
FROM
    production_orders po
INNER JOIN boms b ON
    b.material_id = po.material_id
INNER JOIN bom_records br ON
    b.bom_id = br.bom_id
LEFT OUTER JOIN prod_orders_materials pom ON
    po.po_id = pom.po_id AND pom.material_id = br.material_id
WHERE
    po.status != 'Completed' AND br.material_id = "9bc44474-2f88-4c90-a38d-e21762257a99";



CREATE FUNCTION get_reserved_qty(material_id_key CHAR(36)) RETURNS DECIMAL(10, 5) DETERMINISTIC BEGIN DECLARE total_reserved_qty DECIMAL(10, 5); SELECT SUM( CASE WHEN po.status = 'Pending' THEN po.quantity * br.quantity WHEN po.status != 'Pending' AND ((po.quantity * br.quantity) - IFNULL(pom.quantity, 0)) != 0 THEN ((po.quantity * br.quantity) - IFNULL(pom.quantity, 0)) ELSE 0 END ) INTO total_reserved_qty FROM production_orders po INNER JOIN boms b ON b.material_id = po.material_id INNER JOIN bom_records br ON b.bom_id = br.bom_id LEFT OUTER JOIN prod_orders_materials pom ON po.po_id = pom.po_id AND pom.material_id = br.material_id WHERE po.status != 'Completed' AND br.material_id = material_id_key; RETURN total_reserved_qty; END;

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
            WHEN po.status = 'Pending' THEN po.quantity * br.quantity 
            WHEN po.status != 'Pending' AND ((po.quantity * br.quantity) - IFNULL(pom.quantity, 0)) != 0 THEN ((po.quantity * br.quantity) - IFNULL(pom.quantity, 0)) 
            ELSE 0 
        END AS reserved_qty 
    FROM 
        production_orders po
    INNER JOIN materials m ON po.material_id = m.material_id
    INNER JOIN boms b ON b.material_id = po.material_id
    INNER JOIN bom_records br ON b.bom_id = br.bom_id
    LEFT OUTER JOIN prod_orders_materials pom ON po.po_id = pom.po_id AND pom.material_id = br.material_id
    WHERE 
        po.status != 'Completed' 
        AND br.material_id = material_id_key;
END;