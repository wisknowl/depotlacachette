-- ==============================================================================
-- SCRIPT DE RÉINITIALISATION GLOBALE DU DÉPÔT (RESET TO ZERO)
-- CONSERVE : users, products, clients, suppliers, employees, payment_methods, 
--            categories, formats, packaging_types, client_types, expense_categories,
--            movement_types, cash_accounts
-- PURGE    : Toutes les transactions, dettes, caisses, audits et mouvements de stock
-- ==============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------------------------
-- 1. PURGE DES LIGNES DE DÉTAILS ET MOUVEMENTS (NIVEAU 1 - DÉPENDANCES ENFANTS)
-- ------------------------------------------------------------------------------
DELETE FROM `sale_items`;
ALTER TABLE `sale_items` AUTO_INCREMENT = 1;

DELETE FROM `tournee_items`;
ALTER TABLE `tournee_items` AUTO_INCREMENT = 1;

DELETE FROM `purchase_items`;
ALTER TABLE `purchase_items` AUTO_INCREMENT = 1;

DELETE FROM `stock_movements`;
ALTER TABLE `stock_movements` AUTO_INCREMENT = 1;

DELETE FROM `emballage_movements`;
ALTER TABLE `emballage_movements` AUTO_INCREMENT = 1;

DELETE FROM `cash_transactions`;
ALTER TABLE `cash_transactions` AUTO_INCREMENT = 1;

DELETE FROM `cash_closings`;
ALTER TABLE `cash_closings` AUTO_INCREMENT = 1;

-- ------------------------------------------------------------------------------
-- 2. PURGE DES TRANSACTIONS PARENTES, DETTES ET RÈGLEMENTS (NIVEAU 2)
-- ------------------------------------------------------------------------------
DELETE FROM `client_emballage_debts`;
ALTER TABLE `client_emballage_debts` AUTO_INCREMENT = 1;

DELETE FROM `supplier_emballage_debts`;
ALTER TABLE `supplier_emballage_debts` AUTO_INCREMENT = 1;

DELETE FROM `client_payments`;

DELETE FROM `supplier_payments`;

DELETE FROM `payroll_payments`;

DELETE FROM `employee_ledger`;
ALTER TABLE `employee_ledger` AUTO_INCREMENT = 1;

DELETE FROM `expenses`;

DELETE FROM `sales`;

DELETE FROM `tournees`;
ALTER TABLE `tournees` AUTO_INCREMENT = 1;

DELETE FROM `purchases`;

DELETE FROM `system_audit_logs`;
ALTER TABLE `system_audit_logs` AUTO_INCREMENT = 1;

-- ------------------------------------------------------------------------------
-- 3. REMISE À ZÉRO DU STOCK MATÉRIEL D'EMBALLAGES VIDES (NIVEAU 3)
-- ------------------------------------------------------------------------------
-- Réinitialise les casiers et bouteilles vides du magasin à 0 pour tous les types
UPDATE `emballage_stock` 
SET `empty_crates` = 0, 
    `loose_bottles` = 0;

-- S'assure que chaque type d'emballage configuré possède bien sa ligne à 0
INSERT INTO `emballage_stock` (`packaging_type_id`, `empty_crates`, `loose_bottles`)
SELECT `id`, 0, 0 FROM `packaging_types`
ON DUPLICATE KEY UPDATE `empty_crates` = 0, `loose_bottles` = 0;

SET FOREIGN_KEY_CHECKS = 1;

-- ==============================================================================
-- FIN DU SCRIPT : La base de données est vierge et prête pour l'exploitation.
-- ==============================================================================
