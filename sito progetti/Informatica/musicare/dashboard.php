<?php
/**
 * Dashboard principale - Musicare
 * Mostra contenuti diversi in base ai ruoli dell'utente
 */

session_start();
require_once 'database.php';
require_once 'tenant_context.php';

$tenant_id = musicare_get_current_tenant_id();

// Controlla se l'utente è loggato
$isLoggedIn = isset($_SESSION['utente_id']);
$userRole = null;
$userPermissions = [];
$userData = null;

if ($isLoggedIn) {
    $id_utente = $_SESSION['utente_id'];
    
    // Leggi i dati dell'utente incluso id_ruolo
    $sql = "SELECT u.id_utente, u.nome, u.cognome, u.email, u.id_tenant, u.id_ruolo, r.nome_ruolo
            FROM utenti u
            LEFT JOIN ruoli r ON u.id_ruolo = r.id_ruolo
            WHERE u.id_utente = ? AND u.id_tenant = ?";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_utente, $tenant_id]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        header("Location: login.php");
        exit;
    }
    
    if ($userData) {
        $userRole = $userData['nome_ruolo'];
        $roleId = $userData['id_ruolo'];
        
        // Leggi i privilegi dell'utente
        $sql_perms = "SELECT p.id_privilegio, p.nome_privilegio, p.descrizione
                      FROM privilegi p
                      INNER JOIN ruolo_privilegi rp ON p.id_privilegio = rp.id_privilegio
                      WHERE rp.id_ruolo = ?";
        try {
            $stmt_perms = $pdo->prepare($sql_perms);
            $stmt_perms->execute([$roleId]);
            $userPermissions = $stmt_perms->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $userPermissions = [];
        }
    }
} else {
    header("Location: login.php");
    exit;
}

// Gestisci logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Musicare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .navbar {
            background: rgba(15, 15, 15, 0.95) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }

        .badge-role {
            font-size: 0.85rem;
            padding: 0.4rem 0.8rem;
        }

        .role-registrato { background-color: #6c757d; }
        .role-pro { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .role-admin { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }

        .dashboard-header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
        }

        .welcome-text {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .user-info {
            font-size: 0.95rem;
            opacity: 0.9;
        }

        .card-feature {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            color: white;
            transition: all 0.3s ease;
        }

        .card-feature:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .card-feature h5 {
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-feature i {
            font-size: 1.5rem;
        }

        .feature-list {
            list-style: none;
            padding: 0;
        }

        .feature-list li {
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .feature-list li:last-child {
            border-bottom: none;
        }

        .feature-list i {
            color: #4facfe;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary-custom:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .permissions-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
        }

        .permission-badge {
            display: inline-block;
            background: rgba(79, 172, 254, 0.3);
            border: 1px solid #4facfe;
            border-radius: 20px;
            padding: 8px 15px;
            margin: 5px;
            font-size: 0.9rem;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-top: 30px;
            margin-bottom: 20px;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .role-specific {
            border-left: 4px solid #4facfe;
        }

        .pro-feature {
            position: relative;
            opacity: 0.7;
        }

        .pro-feature.locked::after {
            content: "🔒 Solo Pro";
            position: absolute;
            top: 10px;
            right: 10px;
            background: #f5576c;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .pro-feature.unlocked {
            opacity: 1;
        }

        .admin-panel {
            background: rgba(79, 172, 254, 0.1);
            border-left: 4px solid #4facfe;
        }
    </style>
    <link href="musicare-theme.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-music-note-beamed"></i> Musicare
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="badge badge-role role-<?php echo $userRole; ?> me-3">
                            <?php echo strtoupper($userRole); ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?logout=true">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <!-- Header Benvenuto -->
        <div class="dashboard-header">
            <div class="welcome-text">
                👋 Benvenuto, <?php echo htmlspecialchars($userData['nome']); ?>!
            </div>
            <div class="user-info">
                <p class="mb-0">Email: <?php echo htmlspecialchars($userData['email']); ?></p>
                <p class="mb-0">Ruolo: <strong><?php echo ucfirst($userRole); ?></strong></p>
                <p class="mb-0">Tenant: <strong><?php echo htmlspecialchars($userData['id_tenant']); ?></strong></p>
            </div>
        </div>

        <!-- CONTENUTO PER RUOLO: REGISTRATO -->
        <?php if ($userRole === 'registrato'): ?>
            <div class="section-title">
                <i class="bi bi-star"></i> Accesso Utente Base
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card-feature">
                        <h5>
                            <i class="bi bi-play-circle"></i> Esercizi Base
                        </h5>
                        <p>Accedi agli esercizi introduttivi per imparare i fondamenti.</p>
                        <a href="categoria_esercizi.php" class="btn btn-primary-custom">
                            <i class="bi bi-arrow-right"></i> Inizia
                        </a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card-feature">
                        <h5>
                            <i class="bi bi-credit-card"></i> Upgrade a Pro
                        </h5>
                        <p>Sblocca contenuti esclusivi e accedi a funzionalità avanzate.</p>
                        <a href="#" class="btn btn-primary-custom">
                            <i class="bi bi-star"></i> Scopri i Vantaggi
                        </a>
                    </div>
                </div>
            </div>

            <div class="permissions-container">
                <h6 class="mb-3"><i class="bi bi-shield-check"></i> I tuoi Privilegi</h6>
                <?php foreach ($userPermissions as $perm): ?>
                    <div class="permission-badge">
                        <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($perm['nome_privilegio']); ?>
                    </div>
                <?php endforeach; ?>
            </div>

        <!-- CONTENUTO PER RUOLO: PRO -->
        <?php elseif ($userRole === 'pro'): ?>
            <div class="section-title">
                <i class="bi bi-star-fill" style="color: #f5576c;"></i> Area Pro
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="card-feature pro-feature unlocked">
                        <h5>
                            <i class="bi bi-play-circle"></i> Esercizi Avanzati
                        </h5>
                        <p>Accedi a 10 esercizi esclusivi con difficoltà crescente.</p>
                        <a href="categoria_esercizi.php" class="btn btn-primary-custom">
                            <i class="bi bi-arrow-right"></i> Vai agli Esercizi
                        </a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-feature pro-feature unlocked">
                        <h5>
                            <i class="bi bi-graph-up"></i> Statistiche Dettagliate
                        </h5>
                        <p>Visualizza dashboard completa con analisi dei tuoi progressi.</p>
                        <a href="#" class="btn btn-primary-custom">
                            <i class="bi bi-arrow-right"></i> Statistiche
                        </a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-feature pro-feature unlocked">
                        <h5>
                            <i class="bi bi-fire"></i> Sfida Quotidiana
                        </h5>
                        <p>Accedi alla sfida personalizzata del giorno.</p>
                        <a href="#" class="btn btn-primary-custom">
                            <i class="bi bi-arrow-right"></i> Accetta Sfida
                        </a>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card-feature pro-feature unlocked">
                        <h5>
                            <i class="bi bi-bookmark"></i> Salva i Tuoi Progressi
                        </h5>
                        <p>Tutti i tuoi risultati e progressi vengono salvati automaticamente nel nostro database.</p>
                        <small class="text-white-50">I dati vengono sincronizzati in tempo reale</small>
                    </div>
                </div>
            </div>

            <div class="permissions-container">
                <h6 class="mb-3"><i class="bi bi-shield-check"></i> I tuoi Privilegi Pro</h6>
                <?php foreach ($userPermissions as $perm): ?>
                    <div class="permission-badge">
                        <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($perm['nome_privilegio']); ?>
                    </div>
                <?php endforeach; ?>
            </div>

        <!-- CONTENUTO PER RUOLO: ADMIN -->
        <?php elseif ($userRole === 'admin'): ?>
            <div class="section-title">
                <i class="bi bi-shield-lock"></i> Pannello Amministratore
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="card-feature admin-panel">
                        <h5>
                            <i class="bi bi-play-circle"></i> Modalita Utente Pro
                        </h5>
                        <p>Accedi al sito come un utente Pro per svolgere gli esercizi e testare le funzionalita.</p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="categoria_esercizi.php" class="btn btn-primary-custom">
                                <i class="bi bi-music-note-beamed"></i> Vai alle categorie
                            </a>
                            <a href="statistiche.php" class="btn btn-primary-custom">
                                <i class="bi bi-graph-up"></i> Visualizza statistiche
                            </a>
                            <a href="rotte_mockup.html" class="btn btn-primary-custom">
                                <i class="bi bi-diagram-3"></i> Viste e Rotte
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card-feature admin-panel">
                        <h5>
                            <i class="bi bi-people"></i> Gestione Utenti
                        </h5>
                        <ul class="feature-list">
                            <li><i class="bi bi-check"></i> Visualizza tutti gli utenti</li>
                            <li><i class="bi bi-check"></i> Modifica ruoli e permessi</li>
                            <li><i class="bi bi-check"></i> Disattiva/Attiva account</li>
                            <li><i class="bi bi-check"></i> Visualizza cronologia login</li>
                        </ul>
                        <a href="#" class="btn btn-primary-custom mt-3">
                            <i class="bi bi-arrow-right"></i> Gestisci Utenti
                        </a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card-feature admin-panel">
                        <h5>
                            <i class="bi bi-sliders"></i> Gestione Esercizi
                        </h5>
                        <ul class="feature-list">
                            <li><i class="bi bi-check"></i> Crea nuovi esercizi</li>
                            <li><i class="bi bi-check"></i> Modifica esercizi esistenti</li>
                            <li><i class="bi bi-check"></i> Gestisci difficoltà</li>
                            <li><i class="bi bi-check"></i> Visualizza statistiche</li>
                        </ul>
                        <a href="#" class="btn btn-primary-custom mt-3">
                            <i class="bi bi-arrow-right"></i> Gestisci Esercizi
                        </a>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card-feature admin-panel">
                        <h5>
                            <i class="bi bi-graph-up"></i> Statistiche Globali
                        </h5>
                        <ul class="feature-list">
                            <li><i class="bi bi-check"></i> Visualizza KPI piattaforma</li>
                            <li><i class="bi bi-check"></i> Analisi di utilizzo</li>
                            <li><i class="bi bi-check"></i> Rapporti mensili</li>
                            <li><i class="bi bi-check"></i> Export dati</li>
                        </ul>
                        <a href="#" class="btn btn-primary-custom mt-3">
                            <i class="bi bi-arrow-right"></i> Statistiche
                        </a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card-feature admin-panel">
                        <h5>
                            <i class="bi bi-lock"></i> Privilegi e Ruoli
                        </h5>
                        <ul class="feature-list">
                            <li><i class="bi bi-check"></i> Gestisci ruoli</li>
                            <li><i class="bi bi-check"></i> Assegna/Revoca privilegi</li>
                            <li><i class="bi bi-check"></i> Configura permessi</li>
                            <li><i class="bi bi-check"></i> Audit log</li>
                        </ul>
                        <a href="#" class="btn btn-primary-custom mt-3">
                            <i class="bi bi-arrow-right"></i> Gestisci Privilegi
                        </a>
                    </div>
                </div>
            </div>

            <div class="permissions-container mt-4">
                <h6 class="mb-3"><i class="bi bi-shield-check"></i> I tuoi Privilegi Admin</h6>
                <?php foreach ($userPermissions as $perm): ?>
                    <div class="permission-badge">
                        <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($perm['nome_privilegio']); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Footer -->
        <div style="text-align: center; margin-top: 50px; color: white; opacity: 0.7;">
            <p>© 2026 Musicare - Piattaforma di Apprendimento Musicale</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
