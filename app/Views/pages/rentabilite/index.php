<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
    <div class="page-title" style="margin-bottom: 0;">
        <i class='bx bx-line-chart'></i>
        <div>
            <h1 style="margin: 0; font-size: 1.6rem;"><?= $title ?></h1>
            <p style="margin: 3px 0 0 0; color: #64748B; font-size: 0.9rem;">
                <i class='bx bx-calendar'></i> Période active : <strong><?= htmlspecialchars($periodLabel) ?></strong>
            </p>
        </div>
    </div>

    <!-- QUICK PERIOD SELECTOR & ACTIONS -->
    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <div style="display: flex; gap: 4px; flex-wrap: wrap; background: #F1F5F9; padding: 4px; border-radius: 10px;">
            <a href="<?= BASE_URL ?>/rentabilite?period=today" class="btn <?= ($period === 'today') ? 'btn-accent' : 'btn-primary' ?>" style="padding: 6px 12px; font-size: 0.82rem; <?= ($period !== 'today') ? 'background: white; color: var(--c-navy); border: 1px solid #CBD5E1;' : '' ?>">
                Aujourd'hui
            </a>
            <a href="<?= BASE_URL ?>/rentabilite?period=week" class="btn <?= ($period === 'week') ? 'btn-accent' : 'btn-primary' ?>" style="padding: 6px 12px; font-size: 0.82rem; <?= ($period !== 'week') ? 'background: white; color: var(--c-navy); border: 1px solid #CBD5E1;' : '' ?>">
                Cette Semaine
            </a>
            <a href="<?= BASE_URL ?>/rentabilite?period=month" class="btn <?= ($period === 'month') ? 'btn-accent' : 'btn-primary' ?>" style="padding: 6px 12px; font-size: 0.82rem; <?= ($period !== 'month') ? 'background: white; color: var(--c-navy); border: 1px solid #CBD5E1;' : '' ?>">
                Ce Mois
            </a>
            <a href="<?= BASE_URL ?>/rentabilite?period=last_month" class="btn <?= ($period === 'last_month') ? 'btn-accent' : 'btn-primary' ?>" style="padding: 6px 12px; font-size: 0.82rem; <?= ($period !== 'last_month') ? 'background: white; color: var(--c-navy); border: 1px solid #CBD5E1;' : '' ?>">
                Mois Dernier
            </a>
            <a href="<?= BASE_URL ?>/rentabilite?period=year" class="btn <?= ($period === 'year') ? 'btn-accent' : 'btn-primary' ?>" style="padding: 6px 12px; font-size: 0.82rem; <?= ($period !== 'year') ? 'background: white; color: var(--c-navy); border: 1px solid #CBD5E1;' : '' ?>">
                Année <?= date('Y') ?>
            </a>
        </div>

        <button onclick="window.print()" class="btn btn-primary" style="padding: 6px 12px; font-size: 0.82rem; background: var(--c-navy); color: white;" title="Imprimer le compte de résultat">
            <i class='bx bx-printer'></i> Imprimer
        </button>

        <button onclick="window.close()" class="btn btn-primary" style="padding: 6px 12px; font-size: 0.82rem; background: #64748B; color: white;" title="Fermer cet onglet">
            <i class='bx bx-x'></i> Fermer
        </button>
    </div>
</div>

<!-- CUSTOM DATE RANGE FILTER -->
<div class="card" style="margin-bottom: 25px; padding: 12px 18px; background: white;">
    <form action="<?= BASE_URL ?>/rentabilite" method="GET" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
        <input type="hidden" name="period" value="custom">
        <span style="font-weight: 700; font-size: 0.88rem; color: var(--c-navy); display: flex; align-items: center; gap: 6px;">
            <i class='bx bx-filter-alt'></i> Période Personnalisée :
        </span>
        <div style="display: flex; align-items: center; gap: 6px;">
            <label style="font-size: 0.82rem; color: #64748B;">Du :</label>
            <input type="date" name="start_date" class="form-control" style="padding: 4px 10px; font-size: 0.85rem;" value="<?= htmlspecialchars($startDate) ?>">
        </div>
        <div style="display: flex; align-items: center; gap: 6px;">
            <label style="font-size: 0.82rem; color: #64748B;">Au :</label>
            <input type="date" name="end_date" class="form-control" style="padding: 4px 10px; font-size: 0.85rem;" value="<?= htmlspecialchars($endDate) ?>">
        </div>
        <button type="submit" class="btn btn-accent" style="padding: 5px 14px; font-size: 0.85rem;">
            <i class='bx bx-check'></i> Appliquer
        </button>
    </form>
</div>

<!-- 6 EXECUTIVE KPI CARDS -->
<div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); margin-bottom: 25px; gap: 15px;">
    
    <!-- 1. CHIFFRE D'AFFAIRES -->
    <div class="stat-card" style="border-top: 4px solid var(--c-navy);">
        <div class="stat-icon" style="background-color: rgba(13, 27, 42, 0.1); color: var(--c-navy);">
            <i class='bx bx-cart'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value" style="font-size: 1.35rem;"><?= number_format($metrics['ca'], 0, ',', ' ') ?> FCFA</div>
            <div class="stat-label">Chiffre d'Affaires Global (CA)</div>
        </div>
    </div>

    <!-- 2. MARGE BRUTE -->
    <div class="stat-card" style="border-top: 4px solid #0284C7;">
        <div class="stat-icon" style="background-color: rgba(2, 132, 199, 0.12); color: #0284C7;">
            <i class='bx bx-trending-up'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value" style="font-size: 1.35rem; color: #0284C7;"><?= number_format($metrics['marge_brute'], 0, ',', ' ') ?> FCFA</div>
            <div class="stat-label">
                Marge Brute Commerciale 
                <span style="display: inline-block; padding: 2px 6px; background: #E0F2FE; color: #0369A1; border-radius: 6px; font-weight: 700; font-size: 0.75rem;">
                    <?= $metrics['taux_marge_brute'] ?>%
                </span>
            </div>
        </div>
    </div>

    <!-- 3. RISTOURNES FOURNISSEURS -->
    <div class="stat-card" style="border-top: 4px solid #10B981;">
        <div class="stat-icon" style="background-color: rgba(16, 185, 129, 0.12); color: #10B981;">
            <i class='bx bx-gift'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value" style="font-size: 1.35rem; color: #059669;">+<?= number_format($metrics['ristournes'], 0, ',', ' ') ?> FCFA</div>
            <div class="stat-label">Ristournes Fournisseurs Acquises</div>
        </div>
    </div>

    <!-- 4. TOTAL CHARGES & PAIE -->
    <div class="stat-card" style="border-top: 4px solid #F59E0B;">
        <div class="stat-icon" style="background-color: rgba(245, 158, 11, 0.12); color: #D97706;">
            <i class='bx bx-wallet-alt'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value" style="font-size: 1.35rem; color: #D97706;">-<?= number_format($metrics['total_charges'], 0, ',', ' ') ?> FCFA</div>
            <div class="stat-label">Charges d'Exploitation & Paie</div>
        </div>
    </div>

    <!-- 5. PERTES SUR CASSE -->
    <div class="stat-card" style="border-top: 4px solid #EF4444;">
        <div class="stat-icon" style="background-color: rgba(239, 68, 68, 0.12); color: #DC2626;">
            <i class='bx bx-x-circle'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value" style="font-size: 1.35rem; color: #DC2626;">-<?= number_format($metrics['pertes_casse'], 0, ',', ' ') ?> FCFA</div>
            <div class="stat-label">Pertes sur Casse & Avaries</div>
        </div>
    </div>

    <!-- 6. BENEFICE NET REEL -->
    <?php $isProfitable = ($metrics['benefice_net'] >= 0); ?>
    <div class="stat-card" style="border-top: 4px solid <?= $isProfitable ? '#047857' : '#DC2626' ?>; background: <?= $isProfitable ? '#F0FDF4' : '#FEF2F2' ?>;">
        <div class="stat-icon" style="background-color: <?= $isProfitable ? 'rgba(4, 120, 87, 0.2)' : 'rgba(220, 38, 38, 0.2)' ?>; color: <?= $isProfitable ? '#047857' : '#DC2626' ?>;">
            <i class='bx <?= $isProfitable ? 'bx-trophy' : 'bx-error-alt' ?>'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value" style="font-size: 1.45rem; font-weight: 900; color: <?= $isProfitable ? '#047857' : '#DC2626' ?>;">
                <?= ($isProfitable ? '+' : '') . number_format($metrics['benefice_net'], 0, ',', ' ') ?> FCFA
            </div>
            <div class="stat-label" style="font-weight: 800; color: <?= $isProfitable ? '#065F46' : '#991B1B' ?>;">
                BÉNÉFICE NET RÉEL (P&L)
                <span style="display: inline-block; padding: 2px 6px; background: <?= $isProfitable ? '#DCFCE7' : '#FEE2E2' ?>; color: <?= $isProfitable ? '#166534' : '#991B1B' ?>; border-radius: 6px; font-weight: 700; font-size: 0.75rem;">
                    <?= $metrics['taux_marge_nette'] ?>%
                </span>
            </div>
        </div>
    </div>
</div>

<!-- DETAILED P&L STATEMENT TABLE -->
<div class="card" style="margin-bottom: 25px;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <span><i class='bx bx-book-open'></i> Compte de Résultat d'Exploitation (Structure P&L du Dépôt)</span>
        <span style="font-size: 0.85rem; font-weight: 600; color: #64748B;">Période : <?= htmlspecialchars($periodLabel) ?></span>
    </div>
    <div class="table-responsive">
        <table class="table" style="margin-bottom: 0;">
            <thead>
                <tr style="background: #F8FAFC;">
                    <th style="min-width: 350px;">Rubrique Comptable / Poste Financier</th>
                    <th style="text-align: right; width: 220px;">Montant (FCFA)</th>
                    <th style="text-align: right; width: 140px;">% du Chiffre d'Affaires</th>
                </tr>
            </thead>
            <tbody>
                <!-- 1. VENTES -->
                <tr>
                    <td style="font-weight: 700; color: var(--c-navy);">
                        <i class='bx bx-plus' style="color: var(--c-success);"></i> 1. Chiffre d'Affaires Brut (Total Ventes Facturées)
                    </td>
                    <td style="text-align: right; font-weight: 800; font-size: 1.05rem;"><?= number_format($metrics['ca'], 0, ',', ' ') ?> FCFA</td>
                    <td style="text-align: right; font-weight: 700; color: #64748B;">100.0 %</td>
                </tr>

                <!-- 2. COGS -->
                <tr style="background: #FFFBFB;">
                    <td style="color: #64748B; padding-left: 30px;">
                        <i class='bx bx-minus' style="color: var(--c-danger);"></i> Coût d'Achat des Marchandises Vendues (COGS)
                    </td>
                    <td style="text-align: right; color: var(--c-danger); font-weight: 600;">-<?= number_format($metrics['cogs'], 0, ',', ' ') ?> FCFA</td>
                    <td style="text-align: right; color: #64748B;"><?= ($metrics['ca'] > 0) ? round(($metrics['cogs'] / $metrics['ca']) * 100, 1) : 0 ?> %</td>
                </tr>

                <!-- 3. MARGE BRUTE -->
                <tr style="background: #F0F9FF; border-top: 2px solid #BAE6FD; border-bottom: 2px solid #BAE6FD;">
                    <td style="font-weight: 800; color: #0369A1; font-size: 1rem;">
                        <i class='bx bx-check'></i> 2. Marge Commerciale Brute
                    </td>
                    <td style="text-align: right; font-weight: 900; color: #0369A1; font-size: 1.1rem;"><?= number_format($metrics['marge_brute'], 0, ',', ' ') ?> FCFA</td>
                    <td style="text-align: right; font-weight: 800; color: #0369A1;"><?= $metrics['taux_marge_brute'] ?> %</td>
                </tr>

                <!-- 4. RISTOURNES -->
                <tr>
                    <td style="font-weight: 600; color: #047857; padding-left: 30px;">
                        <i class='bx bx-plus' style="color: var(--c-success);"></i> Ristournes Fournisseurs Acquises sur Achats
                    </td>
                    <td style="text-align: right; color: #047857; font-weight: 700;">+<?= number_format($metrics['ristournes'], 0, ',', ' ') ?> FCFA</td>
                    <td style="text-align: right; color: #64748B;"><?= ($metrics['ca'] > 0) ? round(($metrics['ristournes'] / $metrics['ca']) * 100, 1) : 0 ?> %</td>
                </tr>

                <!-- 5. MARGE AJUSTEE -->
                <tr style="background: #F8FAFC;">
                    <td style="font-weight: 700; color: var(--c-navy);">
                        <i class='bx bx-right-arrow-alt'></i> 3. Marge Brute Globale Ajustée
                    </td>
                    <td style="text-align: right; font-weight: 800;"><?= number_format($metrics['marge_ajustee'], 0, ',', ' ') ?> FCFA</td>
                    <td style="text-align: right; font-weight: 700; color: #64748B;"><?= ($metrics['ca'] > 0) ? round(($metrics['marge_ajustee'] / $metrics['ca']) * 100, 1) : 0 ?> %</td>
                </tr>

                <!-- 6. CHARGES FIXES -->
                <tr>
                    <td style="color: #64748B; padding-left: 30px;">
                        <i class='bx bx-minus' style="color: var(--c-danger);"></i> Dépenses d'Exploitation & Charges Générales (OPEX)
                    </td>
                    <td style="text-align: right; color: var(--c-danger); font-weight: 600;">-<?= number_format($metrics['depenses'], 0, ',', ' ') ?> FCFA</td>
                    <td style="text-align: right; color: #64748B;"><?= ($metrics['ca'] > 0) ? round(($metrics['depenses'] / $metrics['ca']) * 100, 1) : 0 ?> %</td>
                </tr>

                <!-- 7. MASSE SALARIALE -->
                <tr>
                    <td style="color: #64748B; padding-left: 30px;">
                        <i class='bx bx-minus' style="color: var(--c-danger);"></i> Masse Salariale & Rémunérations Personnel
                    </td>
                    <td style="text-align: right; color: var(--c-danger); font-weight: 600;">-<?= number_format($metrics['masse_salariale'], 0, ',', ' ') ?> FCFA</td>
                    <td style="text-align: right; color: #64748B;"><?= ($metrics['ca'] > 0) ? round(($metrics['masse_salariale'] / $metrics['ca']) * 100, 1) : 0 ?> %</td>
                </tr>

                <!-- 8. PERTES CASSE -->
                <tr>
                    <td style="color: #64748B; padding-left: 30px;">
                        <i class='bx bx-minus' style="color: var(--c-danger);"></i> Pertes sur Casse, Manutention & Avaries
                    </td>
                    <td style="text-align: right; color: var(--c-danger); font-weight: 600;">-<?= number_format($metrics['pertes_casse'], 0, ',', ' ') ?> FCFA</td>
                    <td style="text-align: right; color: #64748B;"><?= ($metrics['ca'] > 0) ? round(($metrics['pertes_casse'] / $metrics['ca']) * 100, 1) : 0 ?> %</td>
                </tr>

                <!-- 9. ECARTS CAISSE -->
                <?php if ($metrics['ecarts_caisse'] != 0): ?>
                <tr>
                    <td style="color: #64748B; padding-left: 30px;">
                        <i class='bx bx-calculator'></i> Écarts de Caisse Rapprochés (Surplus / Déficits Clôtures)
                    </td>
                    <td style="text-align: right; color: <?= ($metrics['ecarts_caisse'] > 0) ? 'var(--c-success)' : 'var(--c-danger)' ?>; font-weight: 600;">
                        <?= ($metrics['ecarts_caisse'] > 0 ? '+' : '') . number_format($metrics['ecarts_caisse'], 0, ',', ' ') ?> FCFA
                    </td>
                    <td style="text-align: right; color: #64748B;">-</td>
                </tr>
                <?php endif; ?>

                <!-- 10. BENEFICE NET -->
                <tr style="background: <?= $isProfitable ? '#ECFDF5' : '#FEF2F2' ?>; border-top: 3px solid <?= $isProfitable ? '#059669' : '#DC2626' ?>;">
                    <td style="font-weight: 900; font-size: 1.15rem; color: <?= $isProfitable ? '#065F46' : '#991B1B' ?>;">
                        <i class='bx bx-award'></i> 4. RÉSULTAT NET D'EXPLOITATION (BÉNÉFICE RÉEL)
                    </td>
                    <td style="text-align: right; font-weight: 900; font-size: 1.35rem; color: <?= $isProfitable ? '#047857' : '#DC2626' ?>;">
                        <?= ($isProfitable ? '+' : '') . number_format($metrics['benefice_net'], 0, ',', ' ') ?> FCFA
                    </td>
                    <td style="text-align: right; font-weight: 900; font-size: 1.1rem; color: <?= $isProfitable ? '#047857' : '#DC2626' ?>;">
                        <?= $metrics['taux_marge_nette'] ?> %
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ANALYTICS SECTION: CHARTS GRID -->
<div class="dashboard-grid" style="grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 25px;">
    
    <!-- CHART 1: MONTHLY PROGRESSION -->
    <div class="card">
        <div class="card-header">
            <span><i class='bx bx-bar-chart-alt-2'></i> Évolution Mensuelle : Chiffre d'Affaires vs Charges vs Bénéfice</span>
        </div>
        <div class="card-body">
            <div style="display: flex; gap: 20px; margin-bottom: 15px; font-size: 0.82rem; font-weight: 700;">
                <span style="display: flex; align-items: center; gap: 5px;">
                    <span style="display: inline-block; width: 12px; height: 12px; background: var(--c-navy); border-radius: 3px;"></span> Chiffre d'Affaires (CA)
                </span>
                <span style="display: flex; align-items: center; gap: 5px;">
                    <span style="display: inline-block; width: 12px; height: 12px; background: #F59E0B; border-radius: 3px;"></span> Total Charges
                </span>
                <span style="display: flex; align-items: center; gap: 5px;">
                    <span style="display: inline-block; width: 12px; height: 12px; background: #10B981; border-radius: 3px;"></span> Bénéfice Net
                </span>
            </div>

            <!-- SVG BAR CHART -->
            <?php 
                $maxVal = 1000;
                foreach ($monthlyEvolution as $m) {
                    if ($m['ca'] > $maxVal) $maxVal = $m['ca'];
                    if ($m['charges'] > $maxVal) $maxVal = $m['charges'];
                }
            ?>
            <div style="display: flex; justify-content: space-between; align-items: flex-end; height: 200px; padding-top: 20px; border-bottom: 2px solid #E2E8F0; gap: 15px;">
                <?php foreach ($monthlyEvolution as $m): ?>
                    <?php 
                        $hCA = ($maxVal > 0) ? round(($m['ca'] / $maxVal) * 160) : 0;
                        $hCh = ($maxVal > 0) ? round(($m['charges'] / $maxVal) * 160) : 0;
                        $hNet = ($maxVal > 0) ? round((max(0, $m['benefice_net']) / $maxVal) * 160) : 0;
                    ?>
                    <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 6px;">
                        <div style="display: flex; align-items: flex-end; gap: 4px; height: 160px;">
                            <!-- CA BAR -->
                            <div style="width: 14px; height: <?= max(4, $hCA) ?>px; background: var(--c-navy); border-radius: 3px 3px 0 0;" title="CA <?= $m['label'] ?>: <?= number_format($m['ca'], 0, ',', ' ') ?> FCFA"></div>
                            <!-- CHARGES BAR -->
                            <div style="width: 14px; height: <?= max(4, $hCh) ?>px; background: #F59E0B; border-radius: 3px 3px 0 0;" title="Charges <?= $m['label'] ?>: <?= number_format($m['charges'], 0, ',', ' ') ?> FCFA"></div>
                            <!-- NET PROFIT BAR -->
                            <div style="width: 14px; height: <?= max(4, $hNet) ?>px; background: #10B981; border-radius: 3px 3px 0 0;" title="Bénéfice <?= $m['label'] ?>: <?= number_format($m['benefice_net'], 0, ',', ' ') ?> FCFA"></div>
                        </div>
                        <span style="font-size: 0.78rem; font-weight: 700; color: #64748B;"><?= $m['label'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- CHART 2: COST STRUCTURE & BREAKDOWN -->
    <div class="card">
        <div class="card-header">
            <span><i class='bx bx-pie-chart-alt-2'></i> Structure des Coûts</span>
        </div>
        <div class="card-body">
            <div style="margin-bottom: 12px; font-size: 0.9rem; font-weight: 700; color: var(--c-navy);">
                Total Charges : <span style="color: var(--c-danger);"><?= number_format($costBreakdown['total'], 0, ',', ' ') ?> FCFA</span>
            </div>

            <?php if (empty($costBreakdown['items'])): ?>
                <p style="color: #64748B; font-size: 0.85rem; padding: 20px 0; text-align: center;">Aucune charge enregistrée sur cette période.</p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <?php foreach ($costBreakdown['items'] as $cost): ?>
                        <div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.82rem; margin-bottom: 4px;">
                                <span style="font-weight: 600;"><?= htmlspecialchars($cost['label']) ?></span>
                                <span style="font-weight: 800;"><?= number_format($cost['amount'], 0, ',', ' ') ?> FCFA (<?= $cost['percentage'] ?>%)</span>
                            </div>
                            <div style="height: 8px; background: #E2E8F0; border-radius: 4px; overflow: hidden;">
                                <div style="width: <?= $cost['percentage'] ?>%; height: 100%; background: <?= $cost['color'] ?>; border-radius: 4px;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- SEUIL DE RENTABILITE -->
            <div style="margin-top: 20px; padding: 12px; background: #FFFBEB; border-left: 4px solid #F59E0B; border-radius: 6px; font-size: 0.82rem; color: #92400E;">
                <i class='bx bx-target-lock'></i> <strong>Point Mort (Seuil de Rentabilité) :</strong><br>
                Le dépôt doit générer au moins <strong><?= number_format($metrics['point_mort'], 0, ',', ' ') ?> FCFA</strong> de CA pour amortir toutes ses charges fixes.
            </div>
        </div>
    </div>
</div>

<!-- PRODUCT PROFITABILITY RANKING MATRIX -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <span><i class='bx bx-medal'></i> Palmarès de Rentabilité des Boissons (Top Marges Générées)</span>
        <span style="font-size: 0.85rem; font-weight: 600; color: #64748B;">Top 15 Produits</span>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr style="background: #F8FAFC;">
                    <th>Rang</th>
                    <th>Désignation Boisson</th>
                    <th>Catégorie</th>
                    <th>Volume Vendu</th>
                    <th style="text-align: right;">Chiffre d'Affaires</th>
                    <th style="text-align: right;">Coût d'Achat (COGS)</th>
                    <th style="text-align: right;">Marge Brute (FCFA)</th>
                    <th style="text-align: center;">Taux de Marge</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($productRanking)): ?>
                    <tr><td colspan="8" style="text-align: center; padding: 25px; color: #64748B;">Aucune vente enregistrée pour cette période.</td></tr>
                <?php else: ?>
                    <?php $rank = 1; foreach ($productRanking as $p): ?>
                        <tr>
                            <td>
                                <?php if ($rank === 1): ?>
                                    <span style="display: inline-block; width: 24px; height: 24px; background: #FCD34D; color: #92400E; border-radius: 50%; text-align: center; line-height: 24px; font-weight: 900; font-size: 0.8rem;">1</span>
                                <?php elseif ($rank === 2): ?>
                                    <span style="display: inline-block; width: 24px; height: 24px; background: #E2E8F0; color: #334155; border-radius: 50%; text-align: center; line-height: 24px; font-weight: 900; font-size: 0.8rem;">2</span>
                                <?php elseif ($rank === 3): ?>
                                    <span style="display: inline-block; width: 24px; height: 24px; background: #FED7AA; color: #9A3412; border-radius: 50%; text-align: center; line-height: 24px; font-weight: 900; font-size: 0.8rem;">3</span>
                                <?php else: ?>
                                    <span style="color: #64748B; font-weight: 700;"><?= $rank ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($p['name']) ?></strong>
                                <span style="font-size: 0.78rem; color: #64748B; display: block;"><?= htmlspecialchars($p['format_name']) ?></span>
                            </td>
                            <td><span style="padding: 2px 8px; background: var(--c-gray-200); border-radius: 8px; font-size: 0.8rem;"><?= htmlspecialchars($p['category_name']) ?></span></td>
                            <td><span class="badge" style="font-weight: 700;"><?= number_format($p['casiers_sold'], 1, ',', ' ') ?> emb.</span></td>
                            <td style="text-align: right; font-weight: 700;"><?= number_format($p['revenue'], 0, ',', ' ') ?> FCFA</td>
                            <td style="text-align: right; color: #64748B;"><?= number_format($p['cogs'], 0, ',', ' ') ?> FCFA</td>
                            <td style="text-align: right; font-weight: 900; color: #047857; font-size: 1.05rem;">
                                +<?= number_format($p['margin'], 0, ',', ' ') ?> FCFA
                            </td>
                            <td style="text-align: center;">
                                <span style="display: inline-block; padding: 3px 8px; border-radius: 8px; font-size: 0.82rem; font-weight: 800; background: <?= ($p['margin_pct'] >= 20) ? '#DCFCE7; color: #166534;' : (($p['margin_pct'] >= 10) ? '#E0F2FE; color: #0369A1;' : '#FEF3C7; color: #92400E;') ?>">
                                    <?= $p['margin_pct'] ?>%
                                </span>
                            </td>
                        </tr>
                    <?php $rank++; endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
