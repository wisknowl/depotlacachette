-- ==============================================================================
-- SCRIPT DE RÉINITIALISATION GLOBALE DU DÉPÔT (RESET TO ZERO)
-- CONSERVE : users, products, clients, suppliers, employees, payment_methods, categories, formats, packaging_types
-- PURGE    : Toutes les transactions, dettes, caisses et mouvements de stock
-- ==============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------------------------
-- 1. PURGE DES LIGNES DE DÉTAILS ET MOUVEMENTS (NIVEAU 1 - DÉPENDANCES ENFANTS)
-- ------------------------------------------------------------------------------
DELETE FROM `sale_items`;
ALTER TABLE `sale_items` AUTO_INCREMENT = 1;

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

DELETE FROM `supplier_emballage_debts`;

DELETE FROM `client_payments`;
ALTER TABLE `client_payments` AUTO_INCREMENT = 1;

DELETE FROM `supplier_payments`;
ALTER TABLE `supplier_payments` AUTO_INCREMENT = 1;

DELETE FROM `payroll_payments`;
ALTER TABLE `payroll_payments` AUTO_INCREMENT = 1;

DELETE FROM `expenses`;
ALTER TABLE `expenses` AUTO_INCREMENT = 1;

DELETE FROM `sales`;

DELETE FROM `purchases`;

DELETE FROM `system_audit_logs`;
ALTER TABLE `system_audit_logs` AUTO_INCREMENT = 1;

-- ------------------------------------------------------------------------------
-- 3. REMISE À ZÉRO DU STOCK MATÉRIEL D'EMBALLAGES VIDES (NIVEAU 3)
-- ------------------------------------------------------------------------------
-- Réinitialise les casiers et bouteilles vides du magasin à 0
UPDATE `emballage_stock` 
SET `empty_crates` = 0, 
    `loose_bottles` = 0;

SET FOREIGN_KEY_CHECKS = 1;

-- ==============================================================================
-- FIN DU SCRIPT : La base de données est vierge et prête pour l'exploitation.
-- ==============================================================================
