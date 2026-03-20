<?php
/**
 * Pannello Admin - Statistiche Globali
 */

session_start();
require_once 'database.php';

if (!isset($_SESSION['utente_id'])) {
    header("Location: login.php");
    exit;
}

$id_utente = $_SESSION['utente_id'];

// Verifica che sia ADMIN
$sql = "SELECT u.id_ruolo FROM utenti u WHERE u.id_utente = ? AND u.id_ruolo = 3";
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_utente]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        header("Location: dashboard.php");
        exit;
    }
} catch (PDOException $e) {
    header("Location: dashboard.php");
    exit;
}

// Statistiche globali
$sql_utenti_totali = "SELECT COUNT(*) as total FROM utenti";
$utenti_totali = (int) $pdo->query($sql_utenti_totali)->fetch(PDO::FETCH_ASSOC)['total'];

$sql_esercizi_totali = "SELECT COUNT(*) as total FROM esercizi";
$esercizi_totali = (int) $pdo->query($sql_esercizi_totali)->fetch(PDO::FETCH_ASSOC)['total'];

$sql_svolgimenti = "SELECT COUNT(*) as total FROM svolge";
$svolgimenti_totali = (int) $pdo->query($sql_svolgimenti)->fetch(PDO::FETCH_ASSOC)['total'];

// Media punti globale
$sql_media_punti = "SELECT AVG(media_punti) as media FROM progressi";
$media_punti_globale = $pdo->query($sql_media_punti)->fetch(PDO::FETCH_ASSOC)['media'] ?? 0;

// Utenti per ruolo
$sql_ruoli = "SELECT r.nome_ruolo, COUNT(u.id_utente) as count
              FROM utenti u
              LEFT JOIN ruoli r ON u.id_ruolo = r.id_ruolo
              GROUP BY r.nome_ruolo";
$utenti_per_ruolo = $pdo->query($sql_ruoli)->fetchAll(PDO::FETCH_ASSOC);

// Top 10 utenti per punti
$sql_top_utenti = "SELECT u.nome, u.cognome, p.media_punti
                   FROM progressi p
                   JOIN utenti u ON p.id_utente = u.id_utente
                   ORDER BY p.media_punti DESC
                   LIMIT 10";
$top_utenti = $pdo->query($sql_top_utenti)->fetchAll(PDO::FETCH_ASSOC);

// Esercizi più svolti
$sql_esercizi_svolti = "SELECT id_esercizio, COUNT(*) as count
                        FROM svolge
                        GROUP BY id_esercizio
                        ORDER BY count DESC
                        LIMIT 10";
$esercizi_svolti = $pdo->query($sql_esercizi_svolti)->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiche Globali - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
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

        .chart-section h4 {
            margin-bottom: 20px;
            font-weight: 600;
        }

        .table-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 25px;
            color: white;
            margin-bottom: 30px;
        }

        .table-section h4 {
            margin-bottom: 20px;
            font-weight: 600;
        }

        .table {
            margin: 0;
        }

        .table thead {
            background: rgba(0, 0, 0, 0.3);
        }

        .table tbody tr:hover {
            background: rgba(255, 255, 255, 0.05) !important;
        }

        .role-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .role-registrato {
            background: rgba(108, 117, 125, 0.3);
            color: #6c757d;
        }

        .role-pro {
            background: rgba(245, 87, 108, 0.3);
            color: #f5576c;
        }

        .role-admin {
            background: rgba(79, 172, 254, 0.3);
            color: #4facfe;
        }
    </style>
    <link href="musicare-theme.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-music-note-beamed"></i> Musicare - Admin
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
                        <a class="nav-link" href="admin_users.php">Utenti</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_exercises.php">Esercizi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_statistics.php">Statistiche</a>
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
            <i class="bi bi-graph-up"></i> Statistiche Globali Piattaforma
        </h1>

        <!-- Statistiche Principali -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon"><i class="bi bi-people"></i></div>
                <div class="number"><?php echo $utenti_totali; ?></div>
                <div class="label">Utenti Totali</div>
            </div>
            <div class="stat-card">
                <div class="icon"><i class="bi bi-book"></i></div>
                <div class="number"><?php echo $esercizi_totali; ?></div>
                <div class="label">Esercizi</div>
            </div>
            <div class="stat-card">
                <div class="icon"><i class="bi bi-checkmark-circle"></i></div>
                <div class="number"><?php echo $svolgimenti_totali; ?></div>
                <div class="label">Svolgimenti</div>
            </div>
            <div class="stat-card">
                <div class="icon"><i class="bi bi-star"></i></div>
                <div class="number"><?php echo number_format($media_punti_globale, 1); ?></div>
                <div class="label">Media Punti</div>
            </div>
        </div>

        <!-- Grafico Utenti per Ruolo -->
        <div class="chart-section">
            <h4><i class="bi bi-pie-chart"></i> Distribuzione Utenti per Ruolo</h4>
            <div style="max-width: 400px; margin: 0 auto;">
                <canvas id="ruoliChart"></canvas>
            </div>
        </div>

        <!-- Grafico Esercizi Più Svolti -->
        <div class="chart-section">
            <h4><i class="bi bi-bar-chart"></i> Top 10 Esercizi Più Svolti</h4>
            <canvas id="eserciziChart" style="max-height: 400px;"></canvas>
        </div>

        <!-- Top Utenti -->
        <div class="table-section">
            <h4><i class="bi bi-trophy"></i> Top 10 Utenti</h4>
            <div class="table-responsive">
                <table class="table table-dark table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Posizione</th>
                            <th>Nome</th>
                            <th>Media Punti</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_utenti as $key => $utente): ?>
                            <tr>
                                <td>
                                    <?php 
                                        if ($key === 0) echo '🥇';
                                        elseif ($key === 1) echo '🥈';
                                        elseif ($key === 2) echo '🥉';
                                        else echo ($key + 1);
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($utente['nome'] . ' ' . $utente['cognome']); ?></td>
                                <td><?php echo number_format($utente['media_punti'], 1); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Dati per i grafici
        const ruoli = <?php echo json_encode(array_column($utenti_per_ruolo, 'nome_ruolo')); ?>;
        const ruoliCount = <?php echo json_encode(array_column($utenti_per_ruolo, 'count')); ?>;

        // Grafico Ruoli
        const ctxRuoli = document.getElementById('ruoliChart').getContext('2d');
        new Chart(ctxRuoli, {
            type: 'doughnut',
            data: {
                labels: ruoli.map(r => r.charAt(0).toUpperCase() + r.slice(1)),
                datasets: [{
                    data: ruoliCount,
                    backgroundColor: [
                        'rgba(108, 117, 125, 0.8)',
                        'rgba(245, 87, 108, 0.8)',
                        'rgba(79, 172, 254, 0.8)'
                    ],
                    borderColor: ['#6c757d', '#f5576c', '#4facfe'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        labels: { color: '#fff', font: { size: 14 } }
                    }
                }
            }
        });

        // Dati Esercizi
        const eserciziIds = <?php echo json_encode(array_column($esercizi_svolti, 'id_esercizio')); ?>;
        const eserciziCount = <?php echo json_encode(array_column($esercizi_svolti, 'count')); ?>;

        // Grafico Esercizi
        const ctxEsercizi = document.getElementById('eserciziChart').getContext('2d');
        new Chart(ctxEsercizi, {
            type: 'bar',
            data: {
                labels: eserciziIds.map(id => 'Esercizio #' + id),
                datasets: [{
                    label: 'Numero Svolgimenti',
                    data: eserciziCount,
                    backgroundColor: 'rgba(79, 172, 254, 0.8)',
                    borderColor: '#4facfe',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        labels: { color: '#fff' }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(255, 255, 255, 0.1)' },
                        ticks: { color: '#fff' }
                    },
                    y: {
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
