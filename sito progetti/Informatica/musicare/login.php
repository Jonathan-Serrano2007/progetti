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
    $sql = "SELECT id_utente, nome, password, ruolo FROM utenti WHERE email = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // L'utente esiste, verifichiamo la password
        if (password_verify($password, $user['password'])) {
            // Password corretta! Salviamo i dati in sessione
            $_SESSION['utente_id'] = $user['id_utente'];
            $_SESSION['utente_nome'] = $user['nome'];
            $_SESSION['utente_ruolo'] = $user['ruolo'];

            // Reindirizziamo l'utente alla dashboard o home
            header("Location: index.php");
            exit;
        } else {
            $errore = "Password errata.";
        }
    } else {
        $errore = "Nessun utente trovato con questa email.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); width: 350px; }
        h2 { text-align: center; color: #333; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #0056b3; }
        .error { color: red; font-size: 14px; text-align: center; }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Accesso</h2>
    
    <?php if ($errore): ?>
        <p class="error"><?php echo $errore; ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label>Email</label>
        <input type="email" name="email" required placeholder="Inserisci la tua email">
        
        <label>Password</label>
        <input type="password" name="password" required placeholder="Inserisci la tua password">
        
        <button type="submit">Accedi</button>
    </form>
    
    <p style="text-align:center; margin-top:15px; font-size:14px;">
        Non hai un account? <a href="register.php">Registrati qui</a>
    </p>
</div>

</body>
</html>