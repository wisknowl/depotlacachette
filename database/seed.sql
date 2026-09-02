-- ==========================================================
-- DEPOT LA CACHETTE - SEED DATA (CAMEROON CONTEXT)
-- ==========================================================

SET NAMES utf8mb4;

-- 1. Utilisateurs
INSERT INTO `users` (`username`, `password_hash`, `role`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin'),     -- password: password
('kamga_caisse', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Caissier'),
('ndongo_vente', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Vendeur');

-- 2. Categories
INSERT INTO `categories` (`name`) VALUES
('Bières Blondes'),
('Bières Brunes / Stouts'),
('Jus & Sodas'),
('Eaux Minérales'),
('Vins & Spiritueux');

-- 3. Formats
INSERT INTO `formats` (`name`) VALUES
('PM 33cl'),
('GM 65cl'),
('Canette 33cl'),
('Canette 50cl'),
('Bouteille 1.5L'),
('Fût 30L');

-- 4. Payment Methods & Cash Accounts
INSERT INTO `payment_methods` (`name`, `type`) VALUES
('Espèces', 'Cash'),
('Orange Money', 'Mobile Money'),
('MTN MoMo', 'Mobile Money'),
('Virement Bancaire', 'Bank'),
('À Crédit', 'Credit');

INSERT INTO `cash_accounts` (`name`, `payment_method_id`) VALUES
('Caisse Principale', 1),
('Compte Orange Money', 2),
('Compte MTN MoMo', 3),
('Compte Afriland First Bank', 4);

-- 5. Movement Types & Expense Categories
INSERT INTO `movement_types` (`name`, `direction`) VALUES
('Achat Fournisseur', 'IN'),
('Vente Client', 'OUT'),
('Casse / Avarie', 'OUT'),
('Consommation Interne', 'OUT'),
('Ajustement Inventaire (+)', 'IN'),
('Ajustement Inventaire (-)', 'OUT');

INSERT INTO `expense_categories` (`name`) VALUES
('Salaire Personnel'),
('Transport & Logistique'),
('Électricité & Eau (ENEO/CAMWATER)'),
('Taxes & Impôts'),
('Frais Divers');

-- 6. Client Types
INSERT INTO `client_types` (`name`) VALUES
('Bar / Snack'),
('Boutique / Alimentation'),
('Particulier'),
('Client VIP');

-- 7. Suppliers
INSERT INTO `suppliers` (`name`, `address`, `phone`, `contact_name`) VALUES
('Brasseries du Cameroun (SABC)', 'Douala, Ndokoti', '+237 600000001', 'M. Oumarou'),
('Union Camerounaise de Brasseries (UCB)', 'Douala, Bassa', '+237 600000002', 'M. Kadji'),
('Guinness Cameroun (Diageo)', 'Douala, Ndokoti', '+237 600000003', 'Mme Atangana'),
('Sources du Pays (Supermont)', 'Mungo', '+237 600000004', 'M. Talla');

-- 8. Clients
INSERT INTO `clients` (`name`, `address`, `phone`, `client_type_id`, `max_credit`) VALUES
('Snack Bar La Joie', 'Yaoundé, Essos', '+237 670112233', 1, 500000.00),
('Alimentation Le Bonheur', 'Douala, Deido', '+237 690223344', 2, 200000.00),
('M. Eto''o Fils', 'Yaoundé, Bastos', '+237 699998877', 4, 1000000.00),
('Bar Les Amis', 'Bafoussam, Tamdja', '+237 677776655', 1, 100000.00);

-- 9. Products (Prices in FCFA)
-- Factor: Number of bottles in a crate (Casier) => typically 24 for PM, 12 for GM, 24 for Cans
INSERT INTO `products` (`name`, `category_id`, `format_id`, `purchase_price`, `price_casier`, `price_demi`, `price_unite`, `initial_stock`, `alert_stock`, `factor`, `supplier_id`) VALUES
('Castel Beer GM', 1, 2, 6500.00, 7200.00, 3600.00, 600.00, 50, 10, 12, 1),
('Beaufort Lager PM', 1, 1, 6000.00, 7200.00, 3600.00, 300.00, 100, 20, 24, 1),
('Mützig GM', 1, 2, 6500.00, 7200.00, 3600.00, 600.00, 40, 10, 12, 1),
('Guinness GM', 2, 2, 8500.00, 9600.00, 4800.00, 800.00, 30, 10, 12, 3),
('Kadji Beer GM', 1, 2, 6200.00, 7200.00, 3600.00, 600.00, 20, 5, 12, 2),
('Top Pamplemousse PM', 3, 1, 4500.00, 5400.00, 2700.00, 250.00, 80, 15, 24, 1),
('Malta Guinness Canette', 3, 3, 8000.00, 9600.00, 4800.00, 400.00, 50, 10, 24, 3),
('Eau Supermont 1.5L', 4, 5, 1800.00, 2400.00, 1200.00, 400.00, 100, 20, 6, 4);

-- 10. Initial Stock Movements (Injecting the initial stock)
INSERT INTO `stock_movements` (`movement_date`, `product_id`, `format_id`, `movement_type_id`, `quantity`, `stock_equivalent`, `unit_price`, `reference`, `user_id`)
SELECT CURRENT_DATE, id, format_id, 5, initial_stock, initial_stock, purchase_price, 'STOCK_INITIAL', 1
FROM `products` WHERE `initial_stock` > 0;
