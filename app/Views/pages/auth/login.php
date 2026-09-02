<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Dépôt La Cachette</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- BoxIcons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">

    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL ?>/assets/img/favicon_io/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/img/favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= BASE_URL ?>/assets/img/favicon_io/favicon-16x16.png">
    <link rel="manifest" href="<?= BASE_URL ?>/assets/img/favicon_io/site.webmanifest">

    <style>
        /* Custom Amber Scrollbar */
        * {
            scrollbar-width: thin;
            scrollbar-color: #F4A423 rgba(0, 0, 0, 0.05);
        }
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.04);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: #F4A423;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #D98218;
        }

        body.login-body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0D1B2A 0%, #16283C 50%, #1E2D3D 100%);
            padding: 20px;
            font-family: 'Inter', sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            background: #FFFFFF;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-header {
            background: #0D1B2A;
            padding: 30px 25px 25px;
            text-align: center;
            border-bottom: 3px solid var(--c-amber);
        }

        .login-logo {
            max-width: 220px;
            height: auto;
            margin-bottom: 10px;
        }

        .login-subtitle {
            color: #D1D5DB;
            font-size: 0.9rem;
            margin-top: 5px;
        }

        .login-body-content {
            padding: 30px 30px 35px;
        }

        .login-input-group {
            position: relative;
            margin-bottom: 20px;
        }

        .login-input-group i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.25rem;
            color: var(--c-gray-600);
            transition: color 0.2s;
        }

        .login-input-group input {
            width: 100%;
            padding: 12px 14px 12px 45px;
            font-size: 0.95rem;
            border: 1.5px solid var(--c-gray-300);
            border-radius: 8px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            font-family: inherit;
        }

        .login-input-group input:focus {
            border-color: var(--c-navy);
            box-shadow: 0 0 0 3px rgba(22, 40, 60, 0.15);
        }

        .login-input-group input:focus + i {
            color: var(--c-amber);
        }

        .login-btn {
            width: 100%;
            padding: 13px;
            background: var(--c-amber);
            color: #0D1B2A;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.2s, transform 0.1s;
        }

        .login-btn:hover {
            background: var(--c-amber-dark);
        }

        .login-btn:active {
            transform: scale(0.99);
        }

        .login-alert {
            padding: 12px 15px;
            background: #FEE2E2;
            border-left: 4px solid var(--c-danger);
            color: #991B1B;
            border-radius: 6px;
            font-size: 0.88rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .demo-credentials {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px dashed var(--c-gray-300);
        }

        .demo-title {
            font-size: 0.8rem;
            color: var(--c-gray-600);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
            font-weight: 600;
            text-align: center;
        }

        .demo-badges {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .demo-badge {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--c-gray-100);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: background 0.2s;
        }

        .demo-badge:hover {
            background: #E5E7EB;
        }

        .demo-badge strong {
            color: var(--c-navy);
        }

        .demo-badge span {
            color: var(--c-gray-600);
            font-family: monospace;
        }
    </style>
</head>
<body class="login-body">

    <div class="login-card">
        <div class="login-header">
            <img src="<?= BASE_URL ?>/assets/img/logo.jpg" alt="Dépôt La Cachette" class="login-logo" onerror="this.src='https://via.placeholder.com/220x55/F4A423/FFFFFF?text=Dépôt+La+Cachette'">
            <div class="login-subtitle">Système de Gestion Commerciale & Trésorerie</div>
        </div>

        <div class="login-body-content">
            <?php if (!empty($error)): ?>
                <div class="login-alert">
                    <i class='bx bx-error-circle' style="font-size: 1.2rem;"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/auth/processLogin" method="POST">
                <div class="login-input-group">
                    <input type="text" name="username" id="login_username" placeholder="Identifiant / Nom d'utilisateur" required autofocus>
                    <i class='bx bx-user'></i>
                </div>

                <div class="login-input-group">
                    <input type="password" name="password" id="login_password" placeholder="Mot de passe" required>
                    <i class='bx bx-lock-alt'></i>
                </div>

                <button type="submit" class="login-btn">
                    <i class='bx bx-log-in-circle'></i>
                    <span>Se Connecter</span>
                </button>
            </form>

            <div class="demo-credentials">
                <div class="demo-title">Comptes de Démonstration (Cliquez pour remplir)</div>
                <div class="demo-badges">
                    <div class="demo-badge" onclick="fillCredentials('admin', 'admin237')">
                        <strong>👑 Administrateur</strong>
                        <span>admin / admin237</span>
                    </div>
                    <div class="demo-badge" onclick="fillCredentials('kamga_caisse', 'caisse123')">
                        <strong>💰 Caissier</strong>
                        <span>kamga_caisse / caisse123</span>
                    </div>
                    <div class="demo-badge" onclick="fillCredentials('ndongo_vente', 'vente123')">
                        <strong>🛒 Vendeur</strong>
                        <span>ndongo_vente / vente123</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function fillCredentials(user, pass) {
        document.getElementById('login_username').value = user;
        document.getElementById('login_password').value = pass;
    }
    </script>
</body>
</html>
