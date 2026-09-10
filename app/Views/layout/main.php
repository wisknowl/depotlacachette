<?php
/**
 * Layout principal - Dépôt La Cachette
 */
$currentUser = $_SESSION['user'] ?? ['username' => 'admin', 'full_name' => 'Administrateur', 'role' => 'Admin'];
$isProduitsActive = isset($active_menu) && in_array($active_menu, ['produits', 'achats', 'ventes', 'tournees', 'stock', 'emballages']);
$hideSidebar = !empty($hide_sidebar);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) . ' - ' : '' ?>Dépôt La Cachette</title>
    
    <!-- Local Fonts & Icons (100% Offline Compatible) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/inter.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/boxicons.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    
    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL ?>/assets/img/favicon_io/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/img/favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= BASE_URL ?>/assets/img/favicon_io/favicon-16x16.png">
    <link rel="manifest" href="<?= BASE_URL ?>/assets/img/favicon_io/site.webmanifest">
</head>
<body>

    <div class="app-container" id="app-container" style="<?= $hideSidebar ? 'display: block;' : '' ?>">
        
        <?php if (!$hideSidebar): ?>
        <!-- SIDEBAR -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header" style="justify-content: center;">
                <img src="<?= BASE_URL ?>/assets/img/logo.jpg" class="logo-full" alt="Logo" onerror="this.src='https://via.placeholder.com/250x60/F4A423/FFFFFF?text=LC'">
                <img src="<?= BASE_URL ?>/assets/img/fav.jpg" class="logo-icon" alt="Icon" onerror="this.src='https://via.placeholder.com/70x70/F4A423/FFFFFF?text=LC'">
            </div>
            
            <ul class="sidebar-menu">
                <!-- 1. TABLEAU DE BORD -->
                <li>
                    <a href="<?= BASE_URL ?>/home" class="<?= (isset($active_menu) && $active_menu == 'dashboard') ? 'active' : '' ?>">
                        <i class='bx bx-grid-alt'></i>
                        <span>Tableau de bord</span>
                    </a>
                </li>

                <!-- 2. CAISSE & TRESORERIE -->
                <li>
                    <a href="<?= BASE_URL ?>/caisse" class="<?= (isset($active_menu) && $active_menu == 'caisse') ? 'active' : '' ?>">
                        <i class='bx bx-wallet'></i>
                        <span>Caisse & Trésorerie</span>
                    </a>
                </li>

                <!-- 3. DEPENSES -->
                <li>
                    <a href="<?= BASE_URL ?>/depenses" class="<?= (isset($active_menu) && $active_menu == 'depenses') ? 'active' : '' ?>">
                        <i class='bx bx-money-withdraw'></i>
                        <span>Dépenses</span>
                    </a>
                </li>

                <!-- 4. GESTION PRODUITS (DROPDOWN TOGGLE) -->
                <li class="sidebar-dropdown <?= $isProduitsActive ? 'open active' : '' ?>">
                    <a href="javascript:void(0)" class="dropdown-toggle <?= $isProduitsActive ? 'active' : '' ?>" id="gestion-produits-toggle">
                        <i class='bx bx-package'></i>
                        <span>Gestion Produits</span>
                        <i class='bx bx-chevron-down arrow'></i>
                    </a>
                    <ul class="sidebar-submenu" style="<?= $isProduitsActive ? 'display: block;' : 'display: none;' ?>">
                        <li>
                            <a href="<?= BASE_URL ?>/produits" class="<?= (isset($active_menu) && $active_menu == 'produits') ? 'active' : '' ?>">
                                <i class='bx bx-list-ul'></i>
                                <span>Catalogue Produits</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= BASE_URL ?>/emballages" class="<?= (isset($active_menu) && $active_menu == 'emballages') ? 'active' : '' ?>">
                                <i class='bx bx-archive'></i>
                                <span>Parc d'Emballages</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= BASE_URL ?>/achats" class="<?= (isset($active_menu) && $active_menu == 'achats') ? 'active' : '' ?>">
                                <i class='bx bx-store'></i>
                                <span>Achats & Approvis.</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= BASE_URL ?>/ventes" class="<?= (isset($active_menu) && $active_menu == 'ventes') ? 'active' : '' ?>">
                                <i class='bx bx-cart'></i>
                                <span>Ventes & Facturation</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= BASE_URL ?>/tournees" class="<?= (isset($active_menu) && $active_menu == 'tournees') ? 'active' : '' ?>">
                                <i class='bx bx-trip'></i>
                                <span>Ventes Route (Tournées)</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= BASE_URL ?>/stock" class="<?= (isset($active_menu) && $active_menu == 'stock') ? 'active' : '' ?>">
                                <i class='bx bx-box'></i>
                                <span>Mouvements Stock</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- 5. CLIENTS -->
                <li>
                    <a href="<?= BASE_URL ?>/clients" class="<?= (isset($active_menu) && $active_menu == 'clients') ? 'active' : '' ?>">
                        <i class='bx bx-user'></i>
                        <span>Clients</span>
                    </a>
                </li>

                <!-- 6. REGLEMENTS CLIENTS -->
                <li>
                    <a href="<?= BASE_URL ?>/reglements" class="<?= (isset($active_menu) && $active_menu == 'reglements') ? 'active' : '' ?>">
                        <i class='bx bx-check-double'></i>
                        <span>Règlements Clients</span>
                    </a>
                </li>

                <!-- 7. FOURNISSEURS -->
                <li>
                    <a href="<?= BASE_URL ?>/fournisseurs" class="<?= (isset($active_menu) && $active_menu == 'fournisseurs') ? 'active' : '' ?>">
                        <i class='bx bx-store-alt'></i>
                        <span>Fournisseurs</span>
                    </a>
                </li>

                <!-- 8. PAIEMENTS FOURNISSEURS -->
                <li>
                    <a href="<?= BASE_URL ?>/paiements-fournisseurs" class="<?= (isset($active_menu) && $active_menu == 'paiements_fournisseurs') ? 'active' : '' ?>">
                        <i class='bx bx-check-shield'></i>
                        <span>Paiements Fournisseurs</span>
                    </a>
                </li>

                <!-- 9. PAIE DU PERSONNEL -->
                <li>
                    <a href="<?= BASE_URL ?>/payroll" class="<?= (isset($active_menu) && $active_menu == 'payroll') ? 'active' : '' ?>">
                        <i class='bx bx-user-check'></i>
                        <span>Paie du Personnel</span>
                    </a>
                </li>

                <!-- 10. AUDIT & CONTROLE DE GESTION -->
                <li>
                    <a href="<?= BASE_URL ?>/audit" class="<?= (isset($active_menu) && $active_menu == 'audit') ? 'active' : '' ?>">
                        <i class='bx bx-shield-quarter'></i>
                        <span>Audit & Sécurité ERP</span>
                    </a>
                </li>


                <!-- 11. ANALYSE DE RENTABILITE & P&L (OPENS IN NEW TAB) -->
                <li>
                    <a href="<?= BASE_URL ?>/rentabilite" target="_blank" class="<?= (isset($active_menu) && $active_menu == 'rentabilite') ? 'active' : '' ?>" title="Ouvrir la rentabilité dans un nouvel onglet">
                        <i class='bx bx-line-chart'></i>
                        <span>Rentabilité & P&L <i class='bx bx-link-external' style="font-size: 0.72rem; vertical-align: super;"></i></span>
                    </a>
                </li>
                
                <!-- 12. USER MANAGEMENT AT BOTTOM -->
                <li style="margin-top: 15px; border-top: 1px solid rgba(255, 255, 255, 0.1); padding-top: 10px;">
                    <a href="<?= BASE_URL ?>/users" class="<?= (isset($active_menu) && $active_menu == 'users') ? 'active' : '' ?>">
                        <i class='bx bx-group'></i>
                        <span>Utilisateurs & Rôles</span>
                    </a>
                </li>
            </ul>
        </aside>
        <?php endif; ?>

        <!-- MAIN CONTENT -->
        <main class="main-content" style="<?= $hideSidebar ? 'margin-left: 0; width: 100%; max-width: 1440px; margin: 0 auto; padding: 20px;' : '' ?>">
            
            <!-- TOP HEADER -->
            <header class="top-header" style="<?= $hideSidebar ? 'margin-bottom: 20px; border-radius: 12px;' : '' ?>">
                <?php if ($hideSidebar): ?>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <img src="<?= BASE_URL ?>/assets/img/fav.jpg" style="height: 36px; width: 36px; border-radius: 8px;" alt="Logo" onerror="this.src='https://via.placeholder.com/36'">
                        <span style="font-weight: 800; font-size: 1.1rem; color: var(--c-navy);">Dépôt La Cachette &mdash; <span style="color: #0284C7; font-size: 0.95rem; font-weight: 700;">Rapport Exécutif de Rentabilité</span></span>
                    </div>
                <?php else: ?>
                    <button class="toggle-btn" id="sidebar-toggle">
                        <i class='bx bx-menu'></i>
                    </button>
                <?php endif; ?>
                
                <!-- USER PROFILE WITH DROPDOWN -->
                <div class="user-dropdown-container" id="user-dropdown-container">
                    <div class="user-info" id="user-profile-trigger">
                        <div class="user-text-details">
                            <span class="user-name"><?= htmlspecialchars($currentUser['full_name'] ?: $currentUser['username']) ?></span>
                            <span class="user-role-badge"><?= htmlspecialchars($currentUser['role']) ?></span>
                        </div>
                        <div class="user-avatar-circle">
                            <i class='bx bxs-user'></i>
                        </div>
                        <i class='bx bx-chevron-down dropdown-arrow'></i>
                    </div>

                    <!-- DROPDOWN MENU -->
                    <div class="user-dropdown-menu" id="user-dropdown-menu">
                        <div class="dropdown-header">
                            <div class="dropdown-user-name"><?= htmlspecialchars($currentUser['full_name'] ?: $currentUser['username']) ?></div>
                            <div class="dropdown-user-role"><?= htmlspecialchars($currentUser['role']) ?> &bull; @<?= htmlspecialchars($currentUser['username']) ?></div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="<?= BASE_URL ?>/users" class="dropdown-item">
                            <i class='bx bx-group'></i>
                            <span>Gestion des Utilisateurs</span>
                        </a>
                        <a href="<?= BASE_URL ?>/users/profile" class="dropdown-item">
                            <i class='bx bx-user-circle'></i>
                            <span>Mon Profil & Sécurité</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="<?= BASE_URL ?>/auth/logout" class="dropdown-item text-danger">
                            <i class='bx bx-log-out'></i>
                            <span>Déconnexion</span>
                        </a>
                    </div>
                </div>
            </header>
            
            <!-- PAGE CONTENT -->
            <div class="page-content">
                <?php
                if (isset($contentView) && file_exists($contentView)) {
                    require $contentView;
                } elseif (isset($viewFile) && file_exists($viewFile)) {
                    require $viewFile;
                }
                ?>
            </div>
            
        </main>
    </div>

    <!-- Custom JS -->
    <script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>
