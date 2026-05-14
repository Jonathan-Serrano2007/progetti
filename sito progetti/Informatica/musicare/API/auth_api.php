<?php
/**
 * API Endpoint per l'autenticazione e generazione JWT
 * 
 * Riceve le credenziali (email e password) e ritorna:
 * - Access token JWT valido per 10 minuti
 * - Refresh token JWT valido per 7 giorni, salvato in tabella utenti
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../../../vendor/autoload.php';
require_once '../database.php';
require_once '../tenant_context.php';
require_once 'jwt_config.php';

use Firebase\JWT\JWT;

// Gestione CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Verifica che sia una richiesta POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Metodo non consentito. Usa POST.']);
    exit();
}

// Ottiene i dati dal body della richiesta
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['email']) || !isset($input['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Email e password sono obbligatorie']);
    exit();
}

$email = trim($input['email']);
$password = $input['password'];
$tenant_id = musicare_get_current_tenant_id(false);

// Verifichiamo le credenziali dell'utente
$sql = "SELECT u.id_utente, u.nome, u.password, u.id_tenant 
        FROM utenti u 
        WHERE u.email = ? AND u.id_tenant = ?";
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email, $tenant_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Errore del database']);
    exit();
}

if ($user) {
    // L'utente esiste, verifichiamo la password
    if (password_verify($password, $user['password'])) {
        try {
            // Password corretta! Generiamo i JWT token
            $secret_key = getJwtSecret();

            // Token di accesso - valido per 10 minuti
            $access_token_payload = array(
                'id_utente' => $user['id_utente'],
                'email' => $email,
                'tenant_id' => $user['id_tenant'],
                'iat' => time(),
                'exp' => time() + (10 * 60)
            );

            $access_token = JWT::encode($access_token_payload, $secret_key, 'HS256');

            // Refresh token - valido per 7 giorni
            $refresh_token_payload = array(
                'id_utente' => $user['id_utente'],
                'email' => $email,
                'tenant_id' => $user['id_tenant'],
                'type' => 'refresh',
                'jti' => bin2hex(random_bytes(16)),
                'iat' => time(),
                'exp' => time() + (7 * 24 * 60 * 60)
            );

            $refresh_token = JWT::encode($refresh_token_payload, $secret_key, 'HS256');

            // Salviamo il refresh token nel database per poterlo invalidare/ruotare
            $update_sql = "UPDATE utenti SET refresh_token = ? WHERE id_utente = ? AND id_tenant = ?";
            try {
                $update_stmt = $pdo->prepare($update_sql);
                $update_stmt->execute([$refresh_token, $user['id_utente'], $user['id_tenant']]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Errore del database durante salvataggio refresh token']);
                exit();
            }

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'access_token' => $access_token,
                'refresh_token' => $refresh_token,
                'token_type' => 'Bearer',
                'expires_in' => 600,
                'refresh_expires_in' => 604800,
                'user' => [
                    'id_utente' => $user['id_utente'],
                    'nome' => $user['nome'],
                    'email' => $email,
                    'tenant_id' => $user['id_tenant']
                ]
            ]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Errore interno durante generazione token']);
        }
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Password errata']);
    }
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Nessun utente trovato con questa email']);
}
$pdo = null;
?>
