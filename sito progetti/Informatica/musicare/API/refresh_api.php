<?php
/**
 * API Endpoint per rinnovare i token JWT
 *
 * Riceve il refresh token e, se valido, restituisce:
 * - Nuovo access token (10 minuti)
 * - Nuovo refresh token (7 giorni)
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
use Firebase\JWT\Key;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Metodo non consentito. Usa POST.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['refresh_token'])) {
    http_response_code(400);
    echo json_encode(['error' => 'refresh_token obbligatorio']);
    exit();
}


$refresh_token = $input['refresh_token'];
$secret_key = getJwtSecret();

try {
    $decoded = JWT::decode($refresh_token, new Key($secret_key, 'HS256'));

    if (!isset($decoded->type) || $decoded->type !== 'refresh') {
        http_response_code(401);
        echo json_encode(['error' => 'Token non valido: tipo errato']);
        exit();
    }

    $id_utente = (int) $decoded->id_utente;
    $email = (string) $decoded->email;
    $token_tenant_id = isset($decoded->tenant_id) ? (string)$decoded->tenant_id : '';

    if ($token_tenant_id === '') {
        http_response_code(401);
        echo json_encode(['error' => 'Token non valido: tenant mancante']);
        exit();
    }

    $sql = "SELECT id_utente, nome, email, refresh_token, id_tenant FROM utenti WHERE id_utente = ? AND id_tenant = ?";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_utente, $token_tenant_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            http_response_code(404);
            echo json_encode(['error' => 'Utente non trovato']);
            exit();
        }
        if (empty($user['refresh_token']) || $user['refresh_token'] !== $refresh_token) {
            http_response_code(401);
            echo json_encode(['error' => 'Refresh token non riconosciuto']);
            exit();
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Errore del database']);
        exit();
    }
    $access_token_payload = array(
        'id_utente' => $id_utente,
        'email' => $email,
        'tenant_id' => $token_tenant_id,
        'iat' => time(),
        'exp' => time() + (10 * 60)
    );

    $new_access_token = JWT::encode($access_token_payload, $secret_key, 'HS256');

    $new_refresh_token_payload = array(
        'id_utente' => $id_utente,
        'email' => $email,
        'tenant_id' => $token_tenant_id,
        'type' => 'refresh',
        'jti' => bin2hex(random_bytes(16)),
        'iat' => time(),
        'exp' => time() + (7 * 24 * 60 * 60)
    );

    $new_refresh_token = JWT::encode($new_refresh_token_payload, $secret_key, 'HS256');

    $update_sql = "UPDATE utenti SET refresh_token = ? WHERE id_utente = ? AND id_tenant = ?";
    try {
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute([$new_refresh_token, $id_utente, $token_tenant_id]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Errore del database durante aggiornamento token']);
        exit();
    }

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'access_token' => $new_access_token,
        'refresh_token' => $new_refresh_token,
        'token_type' => 'Bearer',
        'expires_in' => 600,
        'refresh_expires_in' => 604800,
        'user' => [
            'id_utente' => $id_utente,
            'nome' => $user['nome'],
            'email' => $user['email'],
            'tenant_id' => $token_tenant_id
        ]
    ]);
} catch (\Firebase\JWT\ExpiredException $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Refresh token scaduto']);
} catch (\Firebase\JWT\SignatureInvalidException $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Refresh token non valido']);
} catch (\Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Refresh token non valido: ' . $e->getMessage()]);
}

$pdo = null;
?>
