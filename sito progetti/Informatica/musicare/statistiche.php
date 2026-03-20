<?php
/**
 * Pagina Statistiche
 * Accessibile da utenti PRO e ADMIN
 */

session_start();
require_once 'database.php';

if (!isset($_SESSION['utente_id'])) {
    header("Location: login.php");
    exit;
}

$id_utente = $_SESSION['utente_id'];

// Verifica il ruolo
$sql = "SELECT u.id_ruolo, r.nome_ruolo FROM utenti u LEFT JOIN ruoli r ON u.id_ruolo = r.id_ruolo WHERE u.id_utente = ?";
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_utente]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user || !in_array($user['nome_ruolo'], ['pro', 'admin'])) {
        header("Location: dashboard.php");
        exit;
    }
} catch (PDOException $e) {
    header("Location: dashboard.php");
    exit;
}

// Leggi statistiche dell'utente
try {
    $sql = "SELECT * FROM progressi WHERE id_utente = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_utente]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $stats = null;
}

// Leggi i dettagli dei progressi
try {
    $sql_details = "SELECT * FROM svolge WHERE id_utente = ? ORDER BY data_completamento DESC";
    $stmt_details = $pdo->prepare($sql_details);
    $stmt_details->execute([$id_utente]);
    $progressi_details = $stmt_details->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $progressi_details = [];
}

// Calcolo statistiche aggiuntive
$esercizi_completati = count($progressi_details);
$media_punti = $stats ? $stats['media_punti'] : 0;
$tempo_medio = $stats ? $stats['tempo_medio_impiegato'] : 0;

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiche - Musicare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
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
            margin-bottom: 40px;
        }

        .page-title {
            color: white;
            font-weight: 700;
            margin-bottom: 30px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
        }

        .stat-card .number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #4facfe;
            margin-bottom: 10px;
        }

        .stat-card .label {
            font-size: 0.95rem;
            opacity: 0.9;
        }

        .stat-card .icon {
            font-size: 2rem;
            margin-bottom: 10px;
            opacity: 0.8;
        }

        .chart-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 25px;
            color: white;
            margin-bottom: 30px;
        }

        .table-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 25px;
            color: white;
        }

        .difficulty-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .difficulty-facile {
            background: rgba(40, 167, 69, 0.3);
            color: #28a745;
        }

        .difficulty-media {
            background: rgba(255, 193, 7, 0.3);
            color: #ffc107;
        }

        .difficulty-difficile {
            background: rgba(220, 53, 69, 0.3);
            color: #dc3545;
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
                        <a class="nav-link" href="esercizi_base.php">Esercizi Base</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="esercizi_pro.php">Esercizi Pro</a>
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
            <i class="bi bi-graph-up"></i> Dashboard Statistiche
        </h1>

        <!-- Statistiche Principali -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon"><i class="bi bi-checkmark-circle"></i></div>
                <div class="number"><?php echo $esercizi_completati; ?></div>
                <div class="label">Esercizi Completati</div>
            </div>
            <div class="stat-card">
                <div class="icon"><i class="bi bi-star"></i></div>
                <div class="number"><?php echo number_format($media_punti, 1); ?></div>
                <div class="label">Media Punti</div>
            </div>
            <div class="stat-card">
                <div class="icon"><i class="bi bi-clock"></i></div>
                <div class="number"><?php echo number_format($tempo_medio, 1); ?></div>
                <div class="label">Tempo Medio (min)</div>
            </div>
            <div class="stat-card">
                <div class="icon"><i class="bi bi-fire"></i></div>
                <div class="number"><?php echo ($esercizi_completati > 0 ? (int)(($media_punti / 100) * 100) : 0); ?>%</div>
                <div class="label">Tasso Successo</div>
            </div>
        </div>

        <!-- Grafico Progressi -->
        <div class="chart-section">
            <h4><i class="bi bi-bar-chart"></i> Progressi nel Tempo</h4>
            <canvas id="progressChart" style="margin-top: 20px;"></canvas>
        </div>

        <!-- Tabella Dettagli -->
        <div class="table-section">
            <h4><i class="bi bi-list"></i> Dettaglio Esercizi</h4>
            <div class="table-responsive mt-3">
                <table class="table table-dark table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Esercizio</th>
                            <th>Punti</th>
                            <th>Tempo (min)</th>
                            <th>Valutazione</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($progressi_details)): ?>
                            <?php foreach ($progressi_details as $prog): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($prog['data_completamento'])); ?></td>
                                    <td>#<?php echo $prog['id_esercizio']; ?></td>
                                    <td><strong><?php echo $prog['risultato'] ? $prog['risultato'] : '-'; ?></strong></td>
                                    <td><?php echo $prog['tempo_impiegato'] ? $prog['tempo_impiegato'] : '-'; ?></td>
                                    <td>
                                        <?php 
                                            $risultato = $prog['risultato'];
                                            if ($risultato >= 80) {
                                                echo '<span class="badge bg-success">Ottimo</span>';
                                            } elseif ($risultato >= 60) {
                                                echo '<span class="badge bg-info">Buono</span>';
                                            } elseif ($risultato >= 40) {
                                                echo '<span class="badge bg-warning">Discreto</span>';
                                            } else {
                                                echo '<span class="badge bg-danger">Insufficiente</span>';
                                            }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Nessun dato disponibile</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Dati per il grafico
        const progressiData = <?php echo json_encode(array_reverse($progressi_details)); ?>;
        
        const dates = progressiData.map(p => new Date(p.data_completamento).toLocaleDateString('it-IT'));
        const punti = progressiData.map(p => p.risultato || 0);

        const ctx = document.getElementById('progressChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Punti Ottenuti',
                    data: punti,
                    borderColor: '#4facfe',
                    backgroundColor: 'rgba(79, 172, 254, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#4facfe',
                    pointBorderColor: '#fff',
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        labels: { color: '#fff' }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: { color: 'rgba(255, 255, 255, 0.1)' },
                        ticks: { color: '#fff' }
                    },
                    x: {
                        grid: { color: 'rgba(255, 255, 255, 0.1)' },
                        ticks: { color: '#fff' }
                    }
                }
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
