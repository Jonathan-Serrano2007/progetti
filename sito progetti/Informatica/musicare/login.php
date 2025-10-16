<?php
$errore = '';
$success = '';
$file_cred = __DIR__ . '/credenziali.json';
$pepper="SERRANO_SECRET";
if (!file_exists($file_cred)) {
        $utenti=[];
        $utente=new stdClass();
        $utente->username="admin";
        $utente->ruolo="admin";
        $salt=bin2hex(random_bytes(16));
        $utente->password=$salt.hash("sha256",$salt."admin".$pepper);
        $utenti[]=$utente;
        $utente=new stdClass();
        $utente->username="user";
        $utente->ruolo="user";
        $salt=bin2hex(random_bytes(16));
        $utente->password=$salt.hash("sha256",$salt."user".$pepper);
        $utenti[]=$utente;
        $utente=new stdClass();
        $utente->username="seller";
        $utente->ruolo="seller";
        $salt=bin2hex(random_bytes(16));
        $utente->password=$salt.hash("sha256",$salt."seller".$pepper);
        $utenti[]=$utente;
        file_put_contents($file_cred, json_encode($utenti, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}




session_start();
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register'])) {
        // Registrazione nuovo utente
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $credenziali = json_decode(file_get_contents($file_cred), true);
        if (isset($credenziali[$username])) {
            $errore = 'Username già esistente.';
        } else {
            $credenziali[$username] = password_hash($password, PASSWORD_DEFAULT);
            file_put_contents($file_cred, json_encode($credenziali, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $success = 'Account creato con successo! Ora puoi accedere.';
        }
    } else {
        // Login
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $utenti = json_decode(file_get_contents($file_cred), true);
        foreach($utenti as $utente){
            if($utente['username']==$username){
                // Differenzia autenticazione per admin, user, contest
                if(isset($utente['ruolo']) && in_array($utente['ruolo'], ['admin','user','contest','seller'])){
                    // Utenti predefiniti: hash custom
                    $salt=substr($utente['password'],0,32);
                    $hash=$salt.hash("sha256",$salt.$password.$pepper);
                    if($hash==$utente['password']){
                        $_SESSION['loggedin'] = true;
                        $_SESSION['username'] = $username;
                        $_SESSION['ruolo'] = $utente['ruolo'];
                        header('Location: home.php');
                        exit;
                    }
                }else{
                    // Utenti registrati: password_hash
                    if(password_verify($password, $utente['password'])){
                        $_SESSION['loggedin'] = true;
                        $_SESSION['username'] = $username;
                        $_SESSION['ruolo'] = 'contest';
                        header('Location: home.php');
                        exit;
                    }
                }
            }
        }
        $errore = 'Credenziali non valide.';
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Login Musicare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="mb-4 text-center">Login</h2>
                        <?php if ($errore) { echo '<div class="alert alert-danger">'.$errore.'</div>'; } ?>
                        <?php if ($success) { echo '<div class="alert alert-success">'.$success.'</div>'; } ?>
                        <form method="POST" class="mb-3">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="submit" name="login" class="btn btn-primary">Accedi</button>
                                <button type="submit" name="register" class="btn btn-success ms-2">Crea account</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>