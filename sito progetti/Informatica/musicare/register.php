<?php
// Includiamo la tua connessione esistente
require_once 'database.php';

$messaggio = "";
$tipo_messaggio = ""; // Per gestire i colori (rosso per errore, verde per successo)

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $cognome = $_POST['cognome'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Criptiamo la password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Iniziamo una transazione (opzionale ma consigliato per coerenza tra tabelle)
    $mysqli->begin_transaction();

    try {
        // 1. Inserimento nella tabella UTENTI
        $sql_utente = "INSERT INTO utenti (nome, cognome, email, password, ruolo) VALUES (?, ?, ?, ?, 'registrato')";
        $stmt_utente = $mysqli->prepare($sql_utente);
        $stmt_utente->bind_param("ssss", $nome, $cognome, $email, $password_hash);
        $stmt_utente->execute();

        // Recuperiamo l'ID appena creato
        $nuovo_id_utente = $mysqli->insert_id;

        // 2. Inserimento automatico nella tabella PROGRESSI (inizializzazione)
        $sql_progressi = "INSERT INTO progressi (id_utente, media_punti, tempo_medio_impiegato) VALUES (?, 0.00, 0.00)";
        $stmt_progressi = $mysqli->prepare($sql_progressi);
        $stmt_progressi->bind_param("i", $nuovo_id_utente);
        $stmt_progressi->execute();

        // Se tutto è andato bene, confermiamo i dati nel DB
        $mysqli->commit();
        
        $messaggio = "Registrazione completata! Ora puoi effettuare il <a href='login.php'>login</a>.";
        $tipo_messaggio = "success";

    } catch (mysqli_sql_exception $e) {
        // Se c'è un errore, annulliamo tutto
        $mysqli->rollback();
        
        if ($mysqli->errno == 1062) {
            $messaggio = "Errore: questa email è già registrata.";
        } else {
            $messaggio = "Errore durante la registrazione: " . $e->getMessage();
        }
        $tipo_messaggio = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Registrazione</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f2f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .reg-box { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 400px; }
        h2 { text-align: center; color: #1c1e21; margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #4b4f56; }
        input { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background-color: #42b72a; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: bold; }
        button:hover { background-color: #36a420; }
        .msg { padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; }
        .error { background-color: #ffebe9; color: #d93025; border: 1px solid #fa3e3e; }
        .success { background-color: #e7f3ff; color: #1877f2; border: 1px solid #1877f2; }
        .link-login { text-align: center; margin-top: 15px; display: block; color: #1877f2; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>

<div class="reg-box">
    <h2>Crea un account</h2>

    <?php if ($messaggio): ?>
        <div class="msg <?php echo $tipo_messaggio; ?>">
            <?php echo $messaggio; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <label>Nome</label>
        <input type="text" name="nome" placeholder="Il tuo nome" required>

        <label>Cognome</label>
        <input type="text" name="cognome" placeholder="Il tuo cognome" required>

        <label>Email</label>
        <input type="email" name="email" placeholder="esempio@mail.it" required>

        <label>Password</label>
        <input type="password" name="password" placeholder="Scegli una password sicura" required>

        <button type="submit">Registrati</button>
    </form>

    <a href="login.php" class="link-login">Hai già un account? Accedi</a>
</div>

</body>
</html>