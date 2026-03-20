<?php
// Includiamo la connessione al database che hai fornito
require_once 'database.php';

// Avviamo la sessione per mantenere l'utente loggato
session_start();

$errore = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepariamo la query per cercare l'utente tramite email
    $sql = "SELECT u.id_utente, u.nome, u.password, r.nome_ruolo 
        FROM utenti u 
        LEFT JOIN utente_ruolo ur ON u.email = ur.email 
        LEFT JOIN ruoli r ON ur.id_ruolo = r.id_ruolo 
        WHERE u.email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // L'utente esiste, verifichiamo la password
        if (password_verify($password, $user['password'])) {
            // Password corretta! Salviamo i dati in sessione
            $_SESSION['utente_id'] = $user['id_utente'];
            $_SESSION['utente_nome'] = $user['nome'];
            $_SESSION['utente_ruolo'] = $user['nome_ruolo'] ?? null;

            // Reindirizziamo l'utente alla dashboard o home
            header("Location: index.php");
            exit;
        } else {
            $errore = "Password errata.";
        }
    } else {
        $errore = "Nessun utente trovato con questa email.";
    }
    // nulla da chiudere per PDO
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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

        .login-shell {
            width: min(440px, 100%);
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

        .login-box {
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
            margin-top: 1.4rem;
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

        .error {
            color: #ff8f8f;
            background: rgba(255, 82, 82, 0.12);
            border: 1px solid rgba(255, 82, 82, 0.35);
            border-radius: 10px;
            padding: 0.65rem 0.75rem;
            font-size: 0.9rem;
            text-align: center;
            margin-bottom: 0.5rem;
        }

        .register-link {
            margin: 1rem 0 0;
            text-align: center;
            color: #cfcfcf;
            font-size: 0.95rem;
        }

        .register-link a {
            color: #1db954;
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
            color: #1ed760;
        }
    </style>
    <link href="musicare-theme.css" rel="stylesheet">
</head>
<body>

<div class="login-shell">
    <a class="back-link" href="index.php"><i class="bi bi-arrow-left"></i> Torna a Musicare</a>
    <div class="login-box">
        <h2>Accesso</h2>
        <p class="subtitle">Entra nella tua area personale.</p>
        
        <?php if ($errore): ?>
            <p class="error"><?php echo htmlspecialchars($errore, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" required placeholder="Inserisci la tua email">
            
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required placeholder="Inserisci la tua password">
            
            <button type="submit">Accedi</button>
        </form>
        
        <p class="register-link">
            Non hai un account? <a href="register.php">Registrati qui</a>
        </p>
    </div>
</div>

</body>
</html>