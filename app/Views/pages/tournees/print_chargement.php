<!-- ACTIONS BAR (NO PRINT) -->
<div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;" class="no-print">
    <a href="<?= BASE_URL ?>/tournees/details/<?= $tournee['id'] ?>" class="btn btn-primary" style="background-color: var(--c-navy); display: inline-flex; align-items: center; gap: 6px;">
        <i class='bx bx-arrow-back'></i> Retour à la Tournée
    </a>
    <button onclick="window.print()" class="btn btn-accent" style="padding: 10px 20px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px;">
        <i class='bx bx-printer'></i> Imprimer ce Bon de Chargement
    </button>
</div>

<style>
@page {
    size: auto;
    margin: 8mm 12mm;
}
.chargement-container {
    max-width: 900px;
    margin: 0 auto;
    background: #FFFFFF;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    padding: 35px;
    border: 1px solid #E2E8F0;
    color: #1E293B;
}
.chargement-header {
    text-align: center;
    border-bottom: 2px solid #0284C7;
    padding-bottom: 15px;
    margin-bottom: 20px;
}
.chargement-header h1 {
    margin: 0 0 4px 0;
    font-size: 22px;
    font-weight: 900;
    color: var(--c-navy, #0F172A);
    text-transform: uppercase;
    letter-spacing: -0.5px;
}
.chargement-header p {
    margin: 2px 0;
    color: #64748B;
    font-size: 13px;
}
.chargement-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 20px;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 8px;
    padding: 14px 18px;
}
.chargement-info-item {
    margin-bottom: 6px;
}
.chargement-info-label {
    font-weight: 700;
    color: #64748B;
    font-size: 11px;
    text-transform: uppercase;
}
.chargement-info-val {
    font-size: 14px;
    font-weight: 700;
    color: #1E293B;
}
.chargement-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 25px;
}
.chargement-table th, .chargement-table td {
    border: 1px solid #CBD5E1;
    padding: 8px 10px;
    text-align: left;
}
.chargement-table th {
    background: #F1F5F9;
    font-size: 11px;
    text-transform: uppercase;
    color: #475569;
}
.chargement-signatures {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    margin-top: 35px;
    padding-top: 20px;
}
.chargement-sig-box {
    border-top: 1px dashed #94A3B8;
    padding-top: 8px;
    text-align: center;
    font-size: 12px;
    color: #475569;
}
@media print {
    .no-print, .sidebar, .top-header, .header, .page-title, .user-dropdown-container, .toggle-btn {
        display: none !important;
    }
    body, .main-content, .page-content {
        background: #FFF !important;
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
    }
    .chargement-container {
        box-shadow: none !important;
        border: none !important;
        padding: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
    }
}
</style>

<div class="card chargement-container">

    <!-- HEADER -->
    <div class="chargement-header">
        <h1>DÉPÔT LA CACHETTE</h1>
        <p>Commerce Général & Vente de Boissons en Gros et Demi-Gros &bull; Yaoundé, Cameroun</p>
        <h2 style="margin: 10px 0 0 0; color: #0284C7; font-size: 17px; font-weight: 800;">
            BON DE CHARGEMENT CAMION &mdash; TOURNÉE N° <?= htmlspecialchars($tournee['reference']) ?>
        </h2>
    </div>

    <!-- METADATA GRID -->
    <div class="chargement-info-grid">
        <div>
            <div class="chargement-info-item">
                <span class="chargement-info-label">Chauffeur / Livreur :</span>
                <div class="chargement-info-val"><?= htmlspecialchars($tournee['driver_name'] ?: 'Non assigné') ?></div>
            </div>
            <div class="chargement-info-item">
                <span class="chargement-info-label">Véhicule / Engin :</span>
                <div class="chargement-info-val"><?= htmlspecialchars($tournee['vehicle_name'] ?: 'Standard') ?></div>
            </div>
        </div>
        <div>
            <div class="chargement-info-item">
                <span class="chargement-info-label">Date de Chargement :</span>
                <div class="chargement-info-val"><?= date('d/m/Y', strtotime($tournee['tournee_date'])) ?></div>
            </div>
            <div class="chargement-info-item">
                <span class="chargement-info-label">Itinéraire / Zone :</span>
                <div class="chargement-info-val"><?= htmlspecialchars($tournee['notes'] ?: 'Tournée standard') ?></div>
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <table class="chargement-table">
        <thead>
            <tr>
                <th style="width: 40px; text-align: center;">N°</th>
                <th>Désignation de la Boisson</th>
                <th style="text-align: center;">Format</th>
                <th style="text-align: center;">Emballage</th>
                <th style="text-align: center;">Qté Chargée</th>
                <th style="text-align: right;">Prix Unitaire</th>
                <th style="text-align: right;">Montant Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $totQtyEquiv = 0;
            $totVal = 0;
            foreach ($items as $idx => $it): 
                $hasDemi = !empty($it['has_demi']) || ($it['format_type'] === 'demi');
                $qtyCasiers = floatval($it['qty_loaded']);
                $demiPrice = floatval($it['demi_unit_price'] ?? 0);
                $unitPrice = floatval($it['unit_price']);
                $equiv = $qtyCasiers + ($hasDemi ? 0.5 : 0.0);
                $lineTot = ($qtyCasiers * $unitPrice) + ($hasDemi ? $demiPrice : 0.0);
                $totQtyEquiv += $equiv;
                $totVal += $lineTot;
            ?>
            <tr>
                <td style="text-align: center;"><?= $idx + 1 ?></td>
                <td>
                    <strong><?= htmlspecialchars($it['product_name']) ?></strong>
                    <?php if (!empty($it['short_code'])): ?>
                        <small style="color: #64748B;">[<?= htmlspecialchars($it['short_code']) ?>]</small>
                    <?php endif; ?>
                </td>
                <td style="text-align: center;">
                    <?php if ($hasDemi && $qtyCasiers > 0): ?>
                        <span style="font-weight: 700; color: #3730A3;">Casier</span> + <span style="font-weight: 700; color: #92400E;">Demi</span>
                    <?php elseif ($hasDemi && $qtyCasiers == 0): ?>
                        <span style="font-weight: 700; color: #92400E;">Demi-Casier (0.5)</span>
                    <?php else: ?>
                        Casier Entier (1.0)
                    <?php endif; ?>
                </td>
                <td style="text-align: center;"><?= htmlspecialchars($it['packaging_name'] ?: 'Perdu') ?></td>
                <td style="text-align: center; font-size: 14px; font-weight: 800;">
                    <?php if ($hasDemi && $qtyCasiers > 0): ?>
                        <?= number_format($qtyCasiers, 0) ?> c. + 1 demi
                        <div style="font-size: 11px; color: #64748B; font-weight: 600;">(<?= number_format($equiv, 1) ?> eq.)</div>
                    <?php elseif ($hasDemi && $qtyCasiers == 0): ?>
                        1 demi (0.5)
                    <?php else: ?>
                        <?= number_format($qtyCasiers, 0) ?>
                    <?php endif; ?>
                </td>
                <td style="text-align: right;">
                    <?php if ($hasDemi && $qtyCasiers > 0): ?>
                        <div><?= number_format($unitPrice, 0, ',', ' ') ?> F/c.</div>
                        <div style="font-size: 11px; color: #92400E;">+<?= number_format($demiPrice, 0, ',', ' ') ?> F (demi)</div>
                    <?php elseif ($hasDemi && $qtyCasiers == 0): ?>
                        <?= number_format($demiPrice, 0, ',', ' ') ?> F
                    <?php else: ?>
                        <?= number_format($unitPrice, 0, ',', ' ') ?> F
                    <?php endif; ?>
                </td>
                <td style="text-align: right; font-weight: 700;"><?= number_format($lineTot, 0, ',', ' ') ?> F</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background: #F8FAFC; font-weight: bold;">
                <td colspan="4" style="text-align: right; font-size: 13px;">TOTAL CHARGEMENT :</td>
                <td style="text-align: center; font-size: 15px; color: #0284C7; font-weight: 800;"><?= number_format($totQtyEquiv, 1) ?> unités</td>
                <td></td>
                <td style="text-align: right; font-size: 15px; color: #0284C7; font-weight: 800;"><?= number_format($totVal, 0, ',', ' ') ?> FCFA</td>
            </tr>
        </tfoot>
    </table>

    <!-- SIGNATURES -->
    <div class="chargement-signatures">
        <div class="chargement-sig-box">
            <strong>Le Magasinier (Émetteur)</strong><br><br><br>
            Signature & Date
        </div>
        <div class="chargement-sig-box">
            <strong>Le Chauffeur / Livreur (Réceptionnaire)</strong><br>
            <em>« Bon pour prise en charge conforme de la marchandise »</em><br><br>
            Signature & Date
        </div>
    </div>

</div>
