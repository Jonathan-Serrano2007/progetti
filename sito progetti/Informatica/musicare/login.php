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
                            </div>
        <?php
        $errore = '';
        $success = '';
        $file_cred = __DIR__ . '/credenziali.json';
        $pepper = "SERRANO_SECRET";

        // Helper per creare entry legacy (salt + sha256)
        function make_legacy_entry(string $username, string $role, string $pepper): array {
            $salt = bin2hex(random_bytes(16));
            $password = $salt . hash('sha256', $salt . $username . $pepper);
            return [
                'password' => $password,
                'ruolo' => $role,
            ];
        }

        // Se il file non esiste, crealo con utenti predefiniti (formato associativo username => ['password','ruolo'])
        if (!file_exists($file_cred)) {
            $utenti = [];
            $utenti['admin'] = make_legacy_entry('admin', 'admin', $pepper);
            $utenti['user'] = make_legacy_entry('user', 'user', $pepper);
            $utenti['seller'] = make_legacy_entry('seller', 'seller', $pepper);
            file_put_contents($file_cred, json_encode($utenti, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        session_start();
        if (isset($_GET['logout'])) {
            session_destroy();
            header('Location: index.php');
            exit;
        }

        // Carica e normalizza le credenziali: supporta vecchio formato numerico e nuovo formato associativo
        function load_credentials(string $path): array {
            $raw = @file_get_contents($path);
            if ($raw === false) return [];
            $data = json_decode($raw, true);
            if (!is_array($data)) return [];

            // Se il file contiene un array numerico di oggetti (vecchio formato), migra verso formato associativo
            $is_sequential = array_values($data) === $data;
            if ($is_sequential) {
                $migrated = [];
                foreach ($data as $item) {
                    if (!is_array($item)) continue;
                    $u = $item['username'] ?? null;
                    $p = $item['password'] ?? null;
                    $r = $item['ruolo'] ?? 'contest';
                    if ($u && $p) {
                        $migrated[$u] = ['password' => $p, 'ruolo' => $r];
                    }
                }
                // Salva migrazione
                if (!empty($migrated)) {
                    file_put_contents($path, json_encode($migrated, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    return $migrated;
                }
            }

            // Se è già associativo e ha valori string o array, normalizza
            $normalized = [];
            foreach ($data as $key => $val) {
                if (is_string($val)) {
                    // vecchio caso dove username => password_string
                    $normalized[$key] = ['password' => $val, 'ruolo' => 'contest'];
                } elseif (is_array($val)) {
                    $normalized[$key] = [
                        'password' => $val['password'] ?? '',
                        'ruolo' => $val['ruolo'] ?? 'contest'
                    ];
                }
            }
            return $normalized;
        }

        // Salva le credenziali in formato normalizzato
        function save_credentials(string $path, array $creds): bool {
            return file_put_contents($path, json_encode($creds, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['register'])) {
                // Registrazione nuovo utente
                $username = trim($_POST['username'] ?? '');
                $password = $_POST['password'] ?? '';
                if ($username === '' || $password === '') {
                    $errore = 'Username e password richiesti.';
                } else {
                    $credenziali = load_credentials($file_cred);
                    if (isset($credenziali[$username])) {
                        $errore = 'Username già esistente.';
                    } else {
                        $credenziali[$username] = [
                            'password' => password_hash($password, PASSWORD_DEFAULT),
                            'ruolo' => 'contest'
                        ];
                        if (save_credentials($file_cred, $credenziali)) {
                            $success = 'Account creato con successo! Ora puoi accedere.';
                        } else {
                            $errore = 'Impossibile salvare le credenziali.';
                        }
                    }
                }
            } else {
                // Login
                $username = trim($_POST['username'] ?? '');
                $password = $_POST['password'] ?? '';
                if ($username === '' || $password === '') {
                    $errore = 'Inserisci username e password.';
                } else {
                    $credenziali = load_credentials($file_cred);
                    if (!isset($credenziali[$username])) {
                        $errore = 'Credenziali non valide.';
                    } else {
                        $entry = $credenziali[$username];
                        $stored = $entry['password'] ?? '';
                        $role = $entry['ruolo'] ?? 'contest';

                        $ok = false;
                        // Rileva hash legacy: salt (32 hex) + sha256 (64 hex) => 96 hex chars
                        if (is_string($stored) && preg_match('/^[0-9a-f]{96}$/i', $stored)) {
                            $salt = substr($stored, 0, 32);
                            $hash = $salt . hash('sha256', $salt . $password . $pepper);
                            if (hash_equals($hash, $stored)) {
                                $ok = true;
                            }
                        } elseif (is_string($stored) && password_verify($password, $stored)) {
                            $ok = true;
                        }

                        if ($ok) {
                            $_SESSION['loggedin'] = true;
                            $_SESSION['username'] = $username;
                            $_SESSION['ruolo'] = $role;
                            header('Location: home.php');
                            exit;
                        } else {
                            $errore = 'Credenziali non valide.';
                        }
                    }
                }
            }
        }
        ?>