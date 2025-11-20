<?php
// Avvia la sessione (se non già avviata)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se non autenticato, manda alla pagina di login (questa sarà la prima pagina visibile)
if (empty($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Utente autenticato: mostra una semplice home con logout
$username = htmlspecialchars($_SESSION['username'] ?? 'utente');
$ruolo = htmlspecialchars($_SESSION['ruolo'] ?? '');
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Home - Musicare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2>Benvenuto, <?php echo $username; ?>!</h2>
                <p>Ruolo: <?php echo $ruolo; ?></p>
                <a href="login.php?logout=1" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </div>
</body>
</html>