-- ========================================================
-- MIGRATION: UNIFICATION DU SYSTÈME DEMI (CASE À COCHER)
-- ========================================================

ALTER TABLE `sale_items` 
ADD COLUMN `has_demi` TINYINT(1) NOT NULL DEFAULT 0 AFTER `format_type`,
ADD COLUMN `demi_unit_price` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `unit_price`;

ALTER TABLE `tournee_items` 
ADD COLUMN `has_demi` TINYINT(1) NOT NULL DEFAULT 0 AFTER `format_type`,
ADD COLUMN `demi_unit_price` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `unit_price`;

-- Migration des données historiques si existantes
UPDATE `sale_items` 
SET `has_demi` = 1, 
    `demi_unit_price` = `unit_price`, 
    `quantity` = 0 
WHERE `format_type` = 'demi';

UPDATE `tournee_items` 
SET `has_demi` = 1, 
    `demi_unit_price` = `unit_price`, 
    `qty_loaded` = 0 
WHERE `format_type` = 'demi';
