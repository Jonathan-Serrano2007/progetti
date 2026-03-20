<?php
/**
 * Demo transazione su tabella test.
 * Simula:
 * 1) inserimento in "utenti"
 * 2) assegnazione in "utente_ruolo"
 * Se il punto 2 fallisce, il punto 1 viene annullato con rollback.
 */

session_start();
require_once 'database.php';

if (!isset($_SESSION['utente_id'])) {
    header('Location: login.php');
    exit;
}

$messaggio = '';
$tipo = 'info';

// Tabella di simulazione.
$pdo->exec("CREATE TABLE IF NOT EXISTS test (
    id_test INT AUTO_INCREMENT PRIMARY KEY,
    tipo_step ENUM('utenti', 'utente_ruolo') NOT NULL,
    email VARCHAR(190) NOT NULL,
    ruolo_id INT NOT NULL,
    gruppo_tx VARCHAR(64) NOT NULL,
    creato_il TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_step_email (tipo_step, email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $ruoloId = intval($_POST['ruolo_id'] ?? 1);
    $simulaErrore = isset($_POST['simula_errore']);

    if ($email === '') {
        $messaggio = 'Inserisci una email valida.';
        $tipo = 'danger';
    } else {
        try {
            $pdo->beginTransaction();

            // Step 1: simula inserimento in tabella utenti.
            $gruppoTx = bin2hex(random_bytes(8));
            $stmtUtente = $pdo->prepare('INSERT INTO test (tipo_step, email, ruolo_id, gruppo_tx) VALUES (?, ?, ?, ?)');
            $stmtUtente->execute(['utenti', $email, 0, $gruppoTx]);

            // Step 2: simula inserimento in tabella utente_ruolo.
            // Se simula_errore e' attivo, forza errore con ruolo_id NULL su colonna NOT NULL.
            if ($simulaErrore) {
                $stmtRuolo = $pdo->prepare('INSERT INTO test (tipo_step, email, ruolo_id, gruppo_tx) VALUES (?, ?, NULL, ?)');
                $stmtRuolo->execute(['utente_ruolo', $email, $gruppoTx]);
            } else {
                $stmtRuolo = $pdo->prepare('INSERT INTO test (tipo_step, email, ruolo_id, gruppo_tx) VALUES (?, ?, ?, ?)');
                $stmtRuolo->execute(['utente_ruolo', $email, $ruoloId, $gruppoTx]);
            }

            $pdo->commit();
            $messaggio = 'Commit eseguito: utente e assegnazione ruolo salvati insieme.';
            $tipo = 'success';
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $messaggio = 'Rollback eseguito: errore nello step utente_ruolo, nessun record salvato per questa transazione.';
            $tipo = 'warning';
        }
    }
}

$righe = [];
try {
    $stmt = $pdo->query('SELECT id_test, tipo_step, email, ruolo_id, gruppo_tx, creato_il FROM test ORDER BY id_test DESC LIMIT 30');
    $righe = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $righe = [];
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Transazioni - Musicare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Demo transazione su tabella test</h1>
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">Dashboard</a>
    </div>

    <p class="text-muted">Questa pagina simula la registrazione: prima inserimento in "utenti", poi assegnazione in "utente_ruolo". Se il secondo step fallisce, il primo viene annullato.</p>

    <?php if ($messaggio): ?>
        <div class="alert alert-<?php echo htmlspecialchars($tipo); ?>">
            <?php echo htmlspecialchars($messaggio); ?>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-body">
            <form method="POST" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Email (simula utente)</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ruolo ID</label>
                    <input type="number" name="ruolo_id" class="form-control" value="1" min="1" required>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check me-3">
                        <input class="form-check-input" type="checkbox" name="simula_errore" id="simulaErrore">
                        <label class="form-check-label" for="simulaErrore">Simula errore su utente_ruolo</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Esegui transazione</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Ultimi record nella tabella test</div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Step</th>
                    <th>Email</th>
                    <th>Ruolo ID</th>
                    <th>Gruppo TX</th>
                    <th>Data</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($righe)): ?>
                    <tr><td colspan="6" class="text-center text-muted">Nessun record</td></tr>
                <?php else: ?>
                    <?php foreach ($righe as $r): ?>
                        <tr>
                            <td><?php echo (int)$r['id_test']; ?></td>
                            <td><?php echo htmlspecialchars($r['tipo_step']); ?></td>
                            <td><?php echo htmlspecialchars($r['email']); ?></td>
                            <td><?php echo (int)$r['ruolo_id']; ?></td>
                            <td><?php echo htmlspecialchars($r['gruppo_tx']); ?></td>
                            <td><?php echo htmlspecialchars($r['creato_il']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
