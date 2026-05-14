<?php
/**
 * Pannello Admin - Gestione Utenti
 * Accessibile solo da utenti ADMIN
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

// Verifica che sia ADMIN
$sql = "SELECT u.id_ruolo FROM utenti u WHERE u.id_utente = ? AND u.id_ruolo = 3 AND u.id_tenant = ?";
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_utente, $tenant_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        header("Location: dashboard.php");
        exit;
    }
} catch (PDOException $e) {
    header("Location: dashboard.php");
    exit;
}

// Gestisci azioni
$messaggio = '';
$tipo_messaggio = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['azione'])) {
        $azione = $_POST['azione'];
        
        if ($azione === 'cambia_ruolo') {
            $id_utente_target = intval($_POST['id_utente']);
            $nuovo_ruolo = intval($_POST['nuovo_ruolo']);
            
            $sql_update = "UPDATE utenti SET id_ruolo = ? WHERE id_utente = ? AND id_tenant = ?";
            try {
                $stmt_update = $pdo->prepare($sql_update);
                if ($stmt_update->execute([$nuovo_ruolo, $id_utente_target, $tenant_id])) {
                $messaggio = "Ruolo utente aggiornato con successo!";
                $tipo_messaggio = "success";
                } else {
                $messaggio = "Errore nell'aggiornamento del ruolo.";
                $tipo_messaggio = "danger";
                }
            } catch (PDOException $e) {
                $messaggio = "Errore nell'aggiornamento del ruolo.";
                $tipo_messaggio = "danger";
            }
        }
    }
}

// Leggi tutti gli utenti
$sql_utenti = "SELECT u.id_utente, u.nome, u.cognome, u.email, u.data_registrazione, r.nome_ruolo, r.id_ruolo
               FROM utenti u
               LEFT JOIN ruoli r ON u.id_ruolo = r.id_ruolo
               WHERE u.id_tenant = ?
               ORDER BY u.data_registrazione DESC";
try {
    $stmt_utenti = $pdo->prepare($sql_utenti);
    $stmt_utenti->execute([$tenant_id]);
    $utenti = $stmt_utenti->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $utenti = [];
}

// Leggi i ruoli disponibili
$sql_ruoli = "SELECT * FROM ruoli ORDER BY id_ruolo ASC";
try {
    $stmt_ruoli = $pdo->query($sql_ruoli);
    $ruoli = $stmt_ruoli->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $ruoli = [];
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Utenti - Admin</title>
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

        .alert {
            border-radius: 15px;
            border: none;
            margin-bottom: 20px;
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

        .btn-action {
            padding: 5px 12px;
            font-size: 0.85rem;
            border-radius: 15px;
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
            <i class="bi bi-people"></i> Gestione Utenti
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
                <div class="number"><?php echo count($utenti); ?></div>
                <div class="label">Utenti Totali</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo count(array_filter($utenti, fn($u) => $u['nome_ruolo'] === 'registrato')); ?></div>
                <div class="label">Registrati</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo count(array_filter($utenti, fn($u) => $u['nome_ruolo'] === 'pro')); ?></div>
                <div class="label">Pro</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo count(array_filter($utenti, fn($u) => $u['nome_ruolo'] === 'admin')); ?></div>
                <div class="label">Admin</div>
            </div>
        </div>

        <!-- Tabella Utenti -->
        <div class="admin-panel">
            <h4 class="mb-4"><i class="bi bi-list"></i> Elenco Utenti</h4>
            <div class="table-responsive">
                <table class="table table-dark table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Ruolo</th>
                            <th>Data Registrazione</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($utenti as $utente): ?>
                            <tr>
                                <td><?php echo $utente['id_utente']; ?></td>
                                <td><?php echo htmlspecialchars($utente['nome'] . ' ' . $utente['cognome']); ?></td>
                                <td><?php echo htmlspecialchars($utente['email']); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo $utente['nome_ruolo']; ?>">
                                        <?php echo ucfirst($utente['nome_ruolo']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($utente['data_registrazione'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary btn-action" data-bs-toggle="modal" data-bs-target="#modalCambiaRuolo" onclick="preparaCambiaRuolo(<?php echo $utente['id_utente']; ?>, '<?php echo htmlspecialchars($utente['nome']); ?>', <?php echo $utente['id_ruolo']; ?>)">
                                        <i class="bi bi-pencil"></i> Modifica Ruolo
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Cambia Ruolo -->
    <div class="modal fade" id="modalCambiaRuolo" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-white border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">Cambia Ruolo Utente</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <p>Utente: <strong id="nomeUtente"></strong></p>
                        <div class="mb-3">
                            <label for="nuovoRuolo" class="form-label">Nuovo Ruolo:</label>
                            <select id="nuovoRuolo" name="nuovo_ruolo" class="form-select bg-secondary text-white border-secondary">
                                <?php foreach ($ruoli as $ruolo): ?>
                                    <option value="<?php echo $ruolo['id_ruolo']; ?>">
                                        <?php echo ucfirst($ruolo['nome_ruolo']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                        <input type="hidden" id="idUtenteTarget" name="id_utente" value="">
                        <input type="hidden" name="azione" value="cambia_ruolo">
                        <button type="submit" class="btn btn-primary">Conferma</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function preparaCambiaRuolo(idUtente, nomeUtente, ruoloAttuale) {
            document.getElementById('idUtenteTarget').value = idUtente;
            document.getElementById('nomeUtente').textContent = nomeUtente;
            document.getElementById('nuovoRuolo').value = ruoloAttuale;
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
