<?php
/**
 * Pagina Esercizi Base
 * Accessibile da utenti con ruolo registrato e pro
 */

session_start();
require_once 'database.php';
require_once 'tenant_context.php';

$tenant_id = musicare_get_current_tenant_id();

if (!isset($_SESSION['utente_id'])) {
    header("Location: login.php");
    exit;
}

$id_utente = $_SESSION['utente_id'];

// Verifica il ruolo dell'utente
$sql = "SELECT u.id_ruolo FROM utenti u WHERE u.id_utente = ? AND u.id_tenant = ?";
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_utente, $tenant_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        header("Location: dashboard.php");
        exit;
    }
} catch (PDOException $e) {
    header("Location: dashboard.php");
    exit;
}

// Leggi gli esercizi base
$sql_esercizi = "SELECT * FROM esercizi WHERE tipo_esercizio = 'base' AND id_tenant = ? ORDER BY difficolta ASC";
try {
    $stmt_es = $pdo->prepare($sql_esercizi);
    $stmt_es->execute([$tenant_id]);
    $esercizi = $stmt_es->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $esercizi = [];
}

// Leggi i progressi dell'utente
$sql_progress = "SELECT * FROM svolge WHERE id_utente = ? AND id_tenant = ? ORDER BY data_completamento DESC LIMIT 10";
try {
    $stmt_progress = $pdo->prepare($sql_progress);
    $stmt_progress->execute([$id_utente, $tenant_id]);
    $progressi = $stmt_progress->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $progressi = [];
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esercizi Base - Musicare</title>
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
        }

        .container-main {
            margin-top: 30px;
        }

        .exercise-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            color: white;
            transition: all 0.3s ease;
        }

        .exercise-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
        }

        .exercise-card h5 {
            font-weight: 600;
            margin-bottom: 10px;
        }

        .difficulty-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .difficulty-facile {
            background: rgba(40, 167, 69, 0.3);
            color: #28a745;
            border: 1px solid #28a745;
        }

        .difficulty-media {
            background: rgba(255, 193, 7, 0.3);
            color: #ffc107;
            border: 1px solid #ffc107;
        }

        .difficulty-difficile {
            background: rgba(220, 53, 69, 0.3);
            color: #dc3545;
            border: 1px solid #dc3545;
        }

        .btn-svolgi {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-svolgi:hover {
            transform: scale(1.05);
            color: white;
            text-decoration: none;
        }

        .page-title {
            color: white;
            font-weight: 700;
            margin-bottom: 30px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .progress-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 20px;
            color: white;
            margin-top: 40px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .stat-card .number {
            font-size: 2rem;
            font-weight: 700;
            color: #4facfe;
        }

        .stat-card .label {
            font-size: 0.9rem;
            opacity: 0.8;
        }
    </style>
    <link href="musicare-theme.css" rel="stylesheet">
</head>
<body>
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
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?logout=true">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container container-main">
        <h1 class="page-title">
            <i class="bi bi-play-circle"></i> Esercizi Base
        </h1>

        <!-- Statistiche -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="number"><?php echo count($esercizi); ?></div>
                <div class="label">Esercizi Disponibili</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo count($progressi); ?></div>
                <div class="label">Completati</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php 
                    $media = 0;
                    if (!empty($progressi)) {
                        $totale = 0;
                        foreach ($progressi as $p) {
                            if ($p['risultato']) $totale += $p['risultato'];
                        }
                        $media = round($totale / count($progressi), 1);
                    }
                    echo $media;
                ?></div>
                <div class="label">Media Punti</div>
            </div>
        </div>

        <!-- Esercizi -->
        <div class="row">
            <?php foreach ($esercizi as $esercizio): ?>
                <div class="col-md-6">
                    <div class="exercise-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5>Esercizio #<?php echo $esercizio['id_esercizio']; ?></h5>
                                <p class="mb-2">Categoria: <strong><?php echo htmlspecialchars($esercizio['categoria_esercizio']); ?></strong></p>
                                <p class="mb-2">
                                    Difficoltà: 
                                    <?php $stars = rtrim(rtrim(number_format((float)$esercizio['difficolta'], 1, '.', ''), '0'), '.'); ?>
                                    <span class="difficulty-badge">
                                        <?php echo $stars; ?> stelle
                                    </span>
                                </p>
                                <p class="mb-2">⏱️ Tempo disponibile: <?php echo $esercizio['tempo_disponibile']; ?> minuti</p>
                            </div>
                            <a href="#" class="btn btn-svolgi" onclick="svolgiEsercizio(<?php echo $esercizio['id_esercizio']; ?>)">
                                <i class="bi bi-play"></i> Svolgi
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Ultimi Progressi -->
        <div class="progress-section">
            <h4><i class="bi bi-graph-up"></i> Ultimi Progressi</h4>
            <div class="table-responsive mt-3">
                <table class="table table-dark table-striped">
                    <thead>
                        <tr>
                            <th>Esercizio</th>
                            <th>Punti</th>
                            <th>Tempo (min)</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($progressi)): ?>
                            <?php foreach ($progressi as $prog): ?>
                                <tr>
                                    <td>Esercizio #<?php echo $prog['id_esercizio']; ?></td>
                                    <td><?php echo $prog['risultato'] ? $prog['risultato'] : '-'; ?></td>
                                    <td><?php echo $prog['tempo_impiegato'] ? $prog['tempo_impiegato'] : '-'; ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($prog['data_completamento'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">Nessun esercizio completato</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function svolgiEsercizio(idEsercizio) {
            alert('Esercizio #' + idEsercizio + ' - Funzionalità in sviluppo');
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
