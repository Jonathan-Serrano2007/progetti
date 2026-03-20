<?php
/**
 * Pannello Admin - Gestione Esercizi
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

$messaggio = '';
$tipo_messaggio = '';

// Gestisci azioni
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['azione'])) {
        $azione = $_POST['azione'];
        
        if ($azione === 'nuovo_esercizio') {
            $categoria = $_POST['categoria'] ?? '';
            $difficolta = floatval($_POST['difficolta'] ?? 0);
            $tempo = intval($_POST['tempo'] ?? 0);
            $tipo = $_POST['tipo'] ?? 'base';
            
            $sql_insert = "INSERT INTO esercizi (categoria_esercizio, difficolta, tempo_disponibile, tipo_esercizio) 
                          VALUES (?, ?, ?, ?)";
            try {
                $stmt_insert = $pdo->prepare($sql_insert);
                if ($stmt_insert->execute([$categoria, $difficolta, $tempo, $tipo])) {
                    $messaggio = "Esercizio creato con successo!";
                    $tipo_messaggio = "success";
                } else {
                    $messaggio = "Errore nella creazione dell'esercizio.";
                    $tipo_messaggio = "danger";
                }
            } catch (PDOException $e) {
                $messaggio = "Errore nella creazione dell'esercizio.";
                $tipo_messaggio = "danger";
            }
        }
    }
}

// Leggi tutti gli esercizi
$sql_esercizi = "SELECT * FROM esercizi ORDER BY tipo_esercizio DESC, difficolta ASC";
try {
    $stmt_es = $pdo->query($sql_esercizi);
    $esercizi = $stmt_es->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $esercizi = [];
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Esercizi - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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

        .admin-panel {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 25px;
            color: white;
            margin-bottom: 30px;
        }

        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: #4facfe;
            color: white;
            box-shadow: 0 0 0 0.2rem rgba(79, 172, 254, 0.25);
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border: none;
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 600;
        }

        .btn-primary-custom:hover {
            transform: scale(1.05);
            color: white;
        }

        .table-responsive {
            border-radius: 15px;
            overflow: hidden;
        }

        .table {
            margin: 0;
        }

        .table thead {
            background: rgba(0, 0, 0, 0.3);
        }

        .type-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .type-base {
            background: rgba(108, 117, 125, 0.3);
            color: #6c757d;
        }

        .type-avanzato {
            background: rgba(245, 87, 108, 0.3);
            color: #f5576c;
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
        }

        .difficulty-media {
            background: rgba(255, 193, 7, 0.3);
            color: #ffc107;
        }

        .difficulty-difficile {
            background: rgba(220, 53, 69, 0.3);
            color: #dc3545;
        }

        .alert {
            border-radius: 15px;
            border: none;
            margin-bottom: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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
            <i class="bi bi-sliders"></i> Gestione Esercizi
        </h1>

        <?php if ($messaggio): ?>
            <div class="alert alert-<?php echo $tipo_messaggio; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($messaggio); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistiche -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="number"><?php echo count($esercizi); ?></div>
                <div class="label">Esercizi Totali</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo count(array_filter($esercizi, fn($e) => $e['tipo_esercizio'] === 'base')); ?></div>
                <div class="label">Base</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo count(array_filter($esercizi, fn($e) => $e['tipo_esercizio'] === 'avanzato')); ?></div>
                <div class="label">Avanzati</div>
            </div>
        </div>

        <!-- Form Nuovo Esercizio -->
        <div class="admin-panel">
            <h4 class="mb-4"><i class="bi bi-plus-circle"></i> Nuovo Esercizio</h4>
            <form method="POST">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Categoria</label>
                        <input type="text" class="form-control" name="categoria" placeholder="Es. Teoria Musicale" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Difficoltà (stelle)</label>
                        <input type="number" class="form-control" name="difficolta" min="0.5" max="5" step="0.5" value="1" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tempo (min)</label>
                        <input type="number" class="form-control" name="tempo" min="1" value="10" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tipo</label>
                        <select class="form-select" name="tipo" required>
                            <option value="base">Base</option>
                            <option value="avanzato">Avanzato</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <input type="hidden" name="azione" value="nuovo_esercizio">
                        <button type="submit" class="btn btn-primary-custom w-100">
                            <i class="bi bi-plus"></i> Crea Esercizio
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabella Esercizi -->
        <div class="admin-panel">
            <h4 class="mb-4"><i class="bi bi-list"></i> Elenco Esercizi</h4>
            <div class="table-responsive">
                <table class="table table-dark table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Categoria</th>
                            <th>Tipo</th>
                            <th>Difficoltà</th>
                            <th>Tempo (min)</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($esercizi as $esercizio): ?>
                            <tr>
                                <td><?php echo $esercizio['id_esercizio']; ?></td>
                                <td><?php echo htmlspecialchars($esercizio['categoria_esercizio']); ?></td>
                                <td>
                                    <span class="type-badge type-<?php echo $esercizio['tipo_esercizio']; ?>">
                                        <?php echo ucfirst($esercizio['tipo_esercizio']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php $stars = rtrim(rtrim(number_format((float)$esercizio['difficolta'], 1, '.', ''), '0'), '.'); ?>
                                    <span class="difficulty-badge">
                                        <?php echo $stars; ?> stelle
                                    </span>
                                </td>
                                <td><?php echo $esercizio['tempo_disponibile']; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i> Modifica
                                    </button>
                                    <button class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i> Elimina
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
