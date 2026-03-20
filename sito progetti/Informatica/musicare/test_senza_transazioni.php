<?php
/**
 * Demo SENZA transazioni.
 * Obiettivo: mostrare che, se il secondo step fallisce,
 * il primo inserimento resta nel DB.
 *
 * Tabelle create:
 * - test_utenti
 * - test_utenti_ruoli
 */

session_start();
require_once 'database.php';

if (!isset($_SESSION['utente_id'])) {
    header('Location: login.php');
    exit;
}

$messaggio = '';
$tipo = 'info';
$erroreDettaglio = '';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS test_utenti (
        id_test_utente INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(190) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        creato_il TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS test_utenti_ruoli (
        id_test_utente_ruolo INT AUTO_INCREMENT PRIMARY KEY,
        id_test_utente INT NOT NULL,
        ruolo_nome VARCHAR(50) NOT NULL,
        creato_il TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_test_utente
            FOREIGN KEY (id_test_utente)
            REFERENCES test_utenti(id_test_utente)
            ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (Throwable $e) {
    $messaggio = 'Errore nella creazione delle tabelle di test.';
    $tipo = 'danger';
    $erroreDettaglio = $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $azione = $_POST['azione'] ?? '';

    if ($azione === 'pulisci') {
        try {
            $pdo->exec('DELETE FROM test_utenti_ruoli');
            $pdo->exec('DELETE FROM test_utenti');
            $messaggio = 'Tabelle di test pulite.';
            $tipo = 'secondary';
        } catch (Throwable $e) {
            $messaggio = 'Errore durante la pulizia delle tabelle.';
            $tipo = 'danger';
            $erroreDettaglio = $e->getMessage();
        }
    }

    if ($azione === 'simula') {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $messaggio = 'Inserisci email e password.';
            $tipo = 'warning';
        } else {
            try {
                // STEP 1 (senza transazione): inserimento credenziali in test_utenti.
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt1 = $pdo->prepare('INSERT INTO test_utenti (email, password_hash) VALUES (?, ?)');
                $stmt1->execute([$email, $hash]);
                $idNuovoUtente = (int)$pdo->lastInsertId();

                // STEP 2 (errore forzato): inserimento ruolo con valore NULL su campo NOT NULL.
                // Questo simula il fallimento dell'assegnazione ruolo.
                $stmt2 = $pdo->prepare('INSERT INTO test_utenti_ruoli (id_test_utente, ruolo_nome) VALUES (?, NULL)');
                $stmt2->execute([$idNuovoUtente]);

                $messaggio = 'Inserimento completato (non dovrebbe succedere con errore forzato).';
                $tipo = 'success';
            } catch (Throwable $e) {
                $messaggio = 'Errore simulato su test_utenti_ruoli: inserimento ruolo fallito. SENZA transazione, il record in test_utenti resta salvato.';
                $tipo = 'warning';
                $erroreDettaglio = $e->getMessage();
            }
        }
    }
}

$utenti = [];
$ruoli = [];

try {
    $q1 = $pdo->query('SELECT id_test_utente, email, creato_il FROM test_utenti ORDER BY id_test_utente DESC LIMIT 30');
    $utenti = $q1->fetchAll(PDO::FETCH_ASSOC);

    $q2 = $pdo->query('SELECT id_test_utente_ruolo, id_test_utente, ruolo_nome, creato_il FROM test_utenti_ruoli ORDER BY id_test_utente_ruolo DESC LIMIT 30');
    $ruoli = $q2->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $messaggio = 'Errore durante la lettura delle tabelle di test.';
    $tipo = 'danger';
    $erroreDettaglio = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Senza Transazioni - Musicare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Demo senza transazioni</h1>
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">Dashboard</a>
    </div>

    <p class="text-muted mb-4">
        Questa pagina crea le tabelle <strong>test_utenti</strong> e <strong>test_utenti_ruoli</strong> e simula una registrazione senza transazione.
        Il secondo step fallisce apposta, quindi vedrai il record in <strong>test_utenti</strong> ma non in <strong>test_utenti_ruoli</strong>.
    </p>

    <?php if ($messaggio): ?>
        <div class="alert alert-<?php echo htmlspecialchars($tipo); ?>">
            <div><?php echo htmlspecialchars($messaggio); ?></div>
            <?php if ($erroreDettaglio !== ''): ?>
                <small class="d-block mt-2 text-muted"><?php echo htmlspecialchars($erroreDettaglio); ?></small>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-body">
            <form method="POST" class="row g-3">
                <input type="hidden" name="azione" value="simula">
                <div class="col-md-5">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Password</label>
                    <input type="text" name="password" class="form-control" required>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Simula registrazione SENZA transazione</button>
                </div>
            </form>
        </div>
    </div>

    <div class="d-flex justify-content-end mb-3">
        <form method="POST">
            <input type="hidden" name="azione" value="pulisci">
            <button type="submit" class="btn btn-outline-danger btn-sm">Pulisci tabelle test</button>
        </form>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">test_utenti</div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Creato il</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($utenti)): ?>
                            <tr><td colspan="3" class="text-center text-muted">Nessun record</td></tr>
                        <?php else: ?>
                            <?php foreach ($utenti as $u): ?>
                                <tr>
                                    <td><?php echo (int)$u['id_test_utente']; ?></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td><?php echo htmlspecialchars($u['creato_il']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">test_utenti_ruoli</div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>ID Utente</th>
                            <th>Ruolo</th>
                            <th>Creato il</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($ruoli)): ?>
                            <tr><td colspan="4" class="text-center text-muted">Nessun record</td></tr>
                        <?php else: ?>
                            <?php foreach ($ruoli as $r): ?>
                                <tr>
                                    <td><?php echo (int)$r['id_test_utente_ruolo']; ?></td>
                                    <td><?php echo (int)$r['id_test_utente']; ?></td>
                                    <td><?php echo htmlspecialchars($r['ruolo_nome']); ?></td>
                                    <td><?php echo htmlspecialchars($r['creato_il']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
