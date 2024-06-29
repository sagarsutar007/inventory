ALTER TABLE `warehouse` DROP INDEX `warehouse_transaction_id_unique`;
SET @counter = 0;
ALTER TABLE `warehouse` ADD `id` BIGINT NOT NULL AUTO_INCREMENT FIRST, ADD INDEX (`id`);

UPDATE `warehouse` 
SET `id` = @counter := @counter + 1 
ORDER BY `created_at` ASC;

ALTER TABLE `warehouse` ADD `year_max_id` BIGINT NOT NULL DEFAULT '0' AFTER `transaction_id`;

ALTER TABLE `warehouse` CHANGE `transaction_id` `old_transaction_id` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;

ALTER TABLE warehouse
ADD COLUMN transaction_id VARCHAR(20) GENERATED ALWAYS AS (
    CONCAT(
        SUBSTRING(YEAR(created_at), 3, 2),   -- Last two digits of the year
        LPAD(WEEK(created_at), 2, '0'),      -- Week number, padded to 2 digits
        LPAD(DAY(created_at), 2, '0'),       -- Day number, padded to 2 digits
        LPAD(id - year_max_id, 5, '0')       -- Auto-increment ID, padded to 5 digits
    )
) VIRTUAL;

DELIMITER //
CREATE TRIGGER reset_yearly_id BEFORE INSERT ON warehouse
FOR EACH ROW
BEGIN
    DECLARE max_id BIGINT;
    IF NEW.created_at IS NOT NULL THEN
        SELECT COALESCE(MAX(year_max_id), 0) INTO max_id
        FROM warehouse
        WHERE YEAR(created_at) = YEAR(NEW.created_at) - 1;
        SET NEW.year_max_id = max_id;
    END IF;
END;
//
DELIMITER ;

===========
ALTER TABLE `production_orders` DROP INDEX `production_orders_po_number_unique`;

ALTER TABLE `production_orders` ADD `id` BIGINT NOT NULL AUTO_INCREMENT FIRST, ADD INDEX (`id`);

ALTER TABLE `production_orders` ADD `year_max_id` BIGINT NOT NULL DEFAULT '0';

ALTER TABLE `production_orders` CHANGE `po_number` `old_po_number` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;

ALTER TABLE production_orders
ADD COLUMN po_number VARCHAR(20) GENERATED ALWAYS AS (
    CONCAT(
        'PO',
        SUBSTRING(YEAR(record_date), 3, 2),   -- Last two digits of the year
        LPAD(WEEK(record_date), 2, '0'),      -- Week number, padded to 2 digits
        LPAD(DAY(record_date), 2, '0'),       -- Day number, padded to 2 digits
        LPAD(id - year_max_id, 5, '0')       -- Auto-increment ID, padded to 5 digits
    )
) VIRTUAL;

DELIMITER //
CREATE TRIGGER reset_yearly_id_po BEFORE INSERT ON
    production_orders FOR EACH ROW
BEGIN
    DECLARE
        max_id BIGINT; IF NEW.record_date IS NOT NULL THEN
    SELECT
        COALESCE(MAX(year_max_id),
        0)
    INTO max_id
FROM
    production_orders
WHERE
    YEAR(record_date) = YEAR(NEW.record_date) - 1;
SET NEW
    .year_max_id = max_id;
END IF;
END;
// DELIMITER ;

===================



