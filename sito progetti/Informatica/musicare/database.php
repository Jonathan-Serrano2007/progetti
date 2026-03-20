<?php
// Parametri di configurazione
$host = '127.0.0.1';
$db = 'my_serranojonathan';
$user = 'utente_phpmyadmin';
$pass = 'ringraziandoPENNETTA';
$charset = 'utf8mb4';

// Creazione della connessione PDO
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die('Errore di connessione (PDO): ' . $e->getMessage());
}

// Esponiamo solo $pdo: il codice ora deve usare PDO nativo
$GLOBALS['pdo'] = $pdo;
?>