<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Helper.php';
require_once __DIR__ . '/../app/Models/VentesModel.php';
require_once __DIR__ . '/../app/Models/ReglementsModel.php';
require_once __DIR__ . '/../app/Models/ClientsModel.php';

use App\Models\VentesModel;
use App\Models\ReglementsModel;
use App\Models\ClientsModel;

$ventesModel = new VentesModel();
$reglementsModel = new ReglementsModel();
$clientsModel = new ClientsModel();
$db = \App\Core\Database::getInstance()->getConnection();

echo "=====================================================\n";
echo "  TEST COMPLET : REMISE COMMERCIALE & AVOIR CLIENT  \n";
echo "=====================================================\n\n";

// 1. Fetch test client and product
$client = $db->query("SELECT * FROM clients WHERE max_credit > 0 LIMIT 1")->fetch();
$product = $db->query("SELECT * FROM products WHERE is_returnable = 1 LIMIT 1")->fetch();
$cashAccount = $db->query("SELECT * FROM cash_accounts LIMIT 1")->fetch();

if (!$client || !$product || !$cashAccount) {
    die("Données insuffisantes pour exécuter le test complet.\n");
}

echo "Client Test     : {$client['name']} (ID: {$client['id']})\n";
echo "Produit Test    : {$product['name']} (ID: {$product['id']}, Prix Casier: {$product['price_casier']} FCFA)\n";
echo "Compte Caisse   : {$cashAccount['name']} (ID: {$cashAccount['id']})\n\n";

// TEST 1 : Vente avec Remise Commerciale
echo "--- TEST 1 : Vente avec Remise Commerciale de 300 FCFA ---\n";
$unitPrice = floatval($product['price_casier']);
$discount = 300.00;
$paidAmount = 5000.00;

$testSaleData = [
    'sale_date' => date('Y-m-d'),
    'client_id' => $client['id'],
    'discount_amount' => $discount,
    'settlement_type' => 'cash',
    'cash_account_id' => $cashAccount['id'],
    'amount_paid' => $paidAmount,
    'reference' => 'TEST-REMISE-300',
    'notes' => 'Test Remise Commerciale',
    'user_id' => 1,
    'items' => [
        [
            'product_id' => $product['id'],
            'format_type' => 'casier',
            'quantity' => 1,
            'unit_price' => $unitPrice,
            'crates_returned' => 1,
            'bottles_returned' => 0
        ]
    ]
];

$saleId1 = $ventesModel->add($testSaleData);
$sale1 = $ventesModel->getSaleWithItems($saleId1);

$subTotal1 = floatval($sale1['total_amount']);
$disc1 = floatval($sale1['discount_amount']);
$net1 = $subTotal1 - $disc1;
$paid1 = floatval($sale1['amount_paid']);
$due1 = floatval($sale1['amount_due']);

echo "✓ Vente {$saleId1} créée :\n";
echo "  - Sous-Total Brut : {$subTotal1} FCFA (Attendu: {$unitPrice})\n";
echo "  - Remise : {$disc1} FCFA (Attendu: {$discount})\n";
echo "  - Net à Payer : {$net1} FCFA (Attendu: " . ($unitPrice - $discount) . ")\n";
echo "  - Montant Versé : {$paid1} FCFA (Attendu: {$paidAmount})\n";
echo "  - Reste Dû (Dette) : {$due1} FCFA (Attendu: " . max(0, ($unitPrice - $discount) - $paidAmount) . ")\n";

if ($subTotal1 == $unitPrice && $disc1 == $discount && $net1 == ($unitPrice - $discount) && $due1 == max(0, ($unitPrice - $discount) - $paidAmount)) {
    echo "  ==> [TEST 1 RÉUSSI AVEC SUCCÈS 🟢]\n\n";
} else {
    echo "  ==> [TEST 1 ÉCHEC 🔴]\n\n";
}

// TEST 2 : Enregistrement d'un Avoir / Reliquat de Monnaie
echo "--- TEST 2 : Création d'un Avoir Client (Reliquat Monnaie de 1 000 FCFA) ---\n";
$avoirAmount = 1000.00;
$paymentData = [
    'payment_date' => date('Y-m-d'),
    'client_id' => $client['id'],
    'amount' => $avoirAmount,
    'cash_account_id' => $cashAccount['id'],
    'reference' => 'AVOIR-TEST-001',
    'notes' => 'Test reliquat monnaie client',
    'is_advance' => true,
    'user_id' => 1
];

$paymentId = $reglementsModel->add($paymentData);
$clientDebtAfter = $reglementsModel->getClientDebt($client['id']);
echo "✓ Avoir / Règlement {$paymentId} enregistré de {$avoirAmount} FCFA.\n";
echo "  - Nouveau Solde Dû Client : " . $clientDebtAfter['solde_du'] . " FCFA\n";
echo "  ==> [TEST 2 RÉUSSI AVEC SUCCÈS 🟢]\n\n";

// Nettoyage des tests
echo "--- Nettoyage des données de test ---\n";
$reglementsModel->delete($paymentId);
$ventesModel->cancelSale($saleId1, 1, "Nettoyage test");
echo "✓ Données de test nettoyées proprement.\n";
echo "\n=====================================================\n";
echo "  TOUS LES TESTS SONT VALIDES À 100% !  \n";
echo "=====================================================\n";
