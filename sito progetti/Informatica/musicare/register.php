<?php
require_once 'database.php';
require_once 'tenant_context.php';
session_start();

$messaggio = '';
$tipo_messaggio = '';
$dettaglio_errore = '';

$is_admin = isset($_SESSION['utente_ruolo']) && $_SESSION['utente_ruolo'] === 'admin';

// Solo admin puo scegliere tenant custom. Per tutti gli altri: tenant di default.
$tenant_id = $is_admin ? musicare_get_current_tenant_id(false) : musicare_get_default_tenant_id();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $cognome = trim($_POST['cognome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $tenant_input = trim($_POST['tenant'] ?? $tenant_id);
    $tenant_id = $is_admin
        ? musicare_normalize_tenant_id($tenant_input)
        : musicare_get_default_tenant_id();

    if ($tenant_id === null) {
        $messaggio = 'Errore: tenant non valido. Usa solo lettere, numeri, trattino e underscore.';
        $tipo_messaggio = 'error';
    }

    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    if ($tipo_messaggio !== 'error') {
        try {
            $pdo->beginTransaction();

            // Multi-tenancy amministrabile solo da admin: tenant custom creabile solo da sessione admin.
            $sql_tenant_exists = 'SELECT id_tenant FROM tenants WHERE id_tenant = ? LIMIT 1';
            $stmt_tenant_exists = $pdo->prepare($sql_tenant_exists);
            $stmt_tenant_exists->execute([$tenant_id]);
            $tenant_exists = (bool)$stmt_tenant_exists->fetchColumn();

            if (!$tenant_exists) {
                if (!$is_admin) {
                    throw new RuntimeException('Tenant non esistente. Solo un amministratore puo creare nuovi tenant.');
                }

                $sql_tenant_create = 'INSERT INTO tenants (id_tenant, nome_tenant) VALUES (?, ?)';
                $stmt_tenant_create = $pdo->prepare($sql_tenant_create);
                $stmt_tenant_create->execute([$tenant_id, 'Tenant ' . strtoupper($tenant_id)]);
            }

            $sql_utente = 'INSERT INTO utenti (nome, cognome, email, password, id_tenant) VALUES (?, ?, ?, ?, ?)';
            $stmt_utente = $pdo->prepare($sql_utente);
            $stmt_utente->execute([$nome, $cognome, $email, $password_hash, $tenant_id]);

            $nuovo_id_utente = (int)$pdo->lastInsertId();

            $sql_progressi = 'INSERT INTO progressi (id_utente, media_punti, tempo_medio_impiegato, id_tenant) VALUES (?, 0.00, 0.00, ?)';
            $stmt_progressi = $pdo->prepare($sql_progressi);
            $stmt_progressi->execute([$nuovo_id_utente, $tenant_id]);

            $pdo->commit();

            $messaggio = 'Registrazione completata! Ora puoi effettuare il login.';
            $tipo_messaggio = 'success';
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $dettaglio_errore = $e->getMessage();

            $errNo = ($e instanceof PDOException) ? ($e->errorInfo[1] ?? 0) : 0;
            if ($e instanceof RuntimeException) {
                $messaggio = 'Errore: operazione non consentita.';
            } elseif ($errNo == 1062) {
                $messaggio = 'Errore: questa email è già registrata per questo tenant.';
            } else {
                $messaggio = 'Errore durante la registrazione.';
            }

            $tipo_messaggio = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 1.5rem;
            background: radial-gradient(circle at 15% 20%, rgba(29, 185, 84, 0.18) 0%, transparent 35%),
                        linear-gradient(135deg, #0f0f0f 0%, #171717 100%);
            color: #fff;
        }

        .register-shell {
            width: min(470px, 100%);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            color: #b3b3b3;
            text-decoration: none;
            margin-bottom: 1rem;
            transition: color 0.2s ease;
        }

        .back-link:hover {
            color: #1ed760;
        }

        .reg-box {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 16px;
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.35);
            padding: 2rem;
        }

        h2 {
            margin: 0 0 0.3rem;
            text-align: center;
            font-size: 1.8rem;
            font-weight: 700;
        }

        .subtitle {
            margin: 0 0 1.5rem;
            text-align: center;
            color: #b3b3b3;
            font-size: 0.95rem;
        }

        label {
            display: block;
            margin: 0.9rem 0 0.35rem;
            color: #d8d8d8;
            font-size: 0.92rem;
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 0.75rem 0.9rem;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.25);
            background: rgba(255, 255, 255, 0.03);
            color: #fff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        input::placeholder {
            color: #8f8f8f;
        }

        input:focus {
            outline: none;
            border-color: #1db954;
            box-shadow: 0 0 0 3px rgba(29, 185, 84, 0.2);
        }

        button {
            width: 100%;
            margin-top: 1.3rem;
            padding: 0.78rem 1rem;
            background: #1db954;
            color: #fff;
            border: none;
            border-radius: 999px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: background 0.2s ease, transform 0.2s ease;
        }

        button:hover {
            background: #1ed760;
            transform: scale(1.01);
        }

        .msg {
            padding: 0.75rem 0.8rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            text-align: center;
            font-size: 0.9rem;
        }

        .error {
            color: #ff8f8f;
            background: rgba(255, 82, 82, 0.12);
            border: 1px solid rgba(255, 82, 82, 0.35);
        }

        .success {
            color: #9df3bc;
            background: rgba(29, 185, 84, 0.14);
            border: 1px solid rgba(29, 185, 84, 0.4);
        }

        .link-login {
            display: block;
            margin-top: 1rem;
            text-align: center;
            color: #cfcfcf;
            text-decoration: none;
            font-size: 0.95rem;
        }

        .link-login strong {
            color: #1db954;
            font-weight: 600;
        }

        .link-login:hover strong {
            color: #1ed760;
        }
    </style>
    <link href="musicare-theme.css" rel="stylesheet">
</head>
<body>

<div class="register-shell">
    <a class="back-link" href="index.php"><i class="bi bi-arrow-left"></i> Torna a Musicare</a>

    <div class="reg-box">
        <h2>Crea un account</h2>
        <p class="subtitle">Inizia il tuo percorso musicale sulla piattaforma.</p>

        <?php if ($messaggio): ?>
            <div class="msg <?php echo $tipo_messaggio; ?>">
                <?php echo htmlspecialchars($messaggio, ENT_QUOTES, 'UTF-8'); ?>
                <?php if ($tipo_messaggio === 'error' && $dettaglio_errore !== ''): ?>
                    <br><small><?php echo htmlspecialchars($dettaglio_errore, ENT_QUOTES, 'UTF-8'); ?></small>
                <?php endif; ?>
                <?php if ($tipo_messaggio === 'success'): ?>
                    <a href="login.php" style="color:#1ed760; font-weight:600; text-decoration:none;"> Vai al login</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <?php if ($is_admin): ?>
                <label for="tenant">Tenant</label>
                <input id="tenant" type="text" name="tenant" required value="<?php echo htmlspecialchars($tenant_id, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Es. public">
            <?php else: ?>
                <input type="hidden" name="tenant" value="<?php echo htmlspecialchars($tenant_id, ENT_QUOTES, 'UTF-8'); ?>">
            <?php endif; ?>

            <label for="nome">Nome</label>
            <input id="nome" type="text" name="nome" placeholder="Il tuo nome" required>

            <label for="cognome">Cognome</label>
            <input id="cognome" type="text" name="cognome" placeholder="Il tuo cognome" required>

            <label for="email">Email</label>
            <input id="email" type="email" name="email" placeholder="esempio@mail.it" required>

            <label for="password">Password</label>
            <input id="password" type="password" name="password" placeholder="Scegli una password sicura" required>

            <button type="submit">Registrati</button>
        </form>

        <a href="login.php" class="link-login">Hai già un account? <strong>Accedi</strong></a>
    </div>
</div>

</body>
</html>
