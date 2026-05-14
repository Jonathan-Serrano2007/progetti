<?php
/**
 * API Endpoint per ottenere i permessi dell'utente
 * 
 * Riceve il JWT come Bearer token e restituisce:
 * - I dati dell'utente
 * - Il ruolo dell'utente
 * - I privilegi associati al ruolo
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../../../../vendor/autoload.php';
require_once '../database.php';
require_once '../tenant_context.php';
require_once 'jwt_config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function extractBearerToken(): ?string
{
    $authorizationHeader = '';

    if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        $authorizationHeader = $_SERVER['HTTP_AUTHORIZATION'];
    } elseif (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $authorizationHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    } elseif (function_exists('getallheaders')) {
        $headers = getallheaders();
        foreach ($headers as $name => $value) {
            if (strtolower($name) === 'authorization') {
                $authorizationHeader = $value;
                break;
            }
        }
    }

    if (!empty($authorizationHeader)) {
        if (preg_match('/^Bearer\s+(.+)$/i', trim($authorizationHeader), $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    // Fallback utile in ambienti in cui Authorization viene filtrato dal server
    if (!empty($_GET['token'])) {
        return trim((string) $_GET['token']);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $rawBody = file_get_contents('php://input');
        if (!empty($rawBody)) {
            $payload = json_decode($rawBody, true);
            if (is_array($payload) && !empty($payload['token'])) {
                return trim((string) $payload['token']);
            }
        }
    }

    return null;
}

// Gestione CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Verifica che sia una richiesta GET o POST
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Metodo non consentito. Usa GET o POST.']);
    exit();
}

// Estrai il token dall'header Authorization (con fallback)
$token = extractBearerToken();

if (empty($token)) {
    http_response_code(401);
    echo json_encode([
        'error' => 'Token mancante. Usa header Authorization: Bearer <token>',
        'hint' => 'In alternativa usa ?token=<jwt> oppure POST JSON {"token":"<jwt>"}'
    ]);
    exit();
}

$secret_key = getJwtSecret();

try {
    // Verifichiamo e decodifichiamo il token
    $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
    
    $id_utente = $decoded->id_utente;
    $email = $decoded->email;
    $token_tenant_id = isset($decoded->tenant_id) ? (string)$decoded->tenant_id : '';

    if ($token_tenant_id === '') {
        http_response_code(401);
        echo json_encode(['error' => 'Token non valido: tenant mancante']);
        exit();
    }
    
    // Query per ottenere i dati dell'utente
    $sql = "SELECT u.id_utente, u.nome, u.cognome, u.email 
            FROM utenti u 
            WHERE u.id_utente = ? AND u.id_tenant = ?";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_utente, $token_tenant_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            http_response_code(404);
            echo json_encode(['error' => 'Utente non trovato']);
            exit();
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Errore del database']);
        exit();
    }

    // Query per ottenere il ruolo dell'utente
    $sql_ruolo = "SELECT r.id_ruolo, r.nome_ruolo 
                  FROM ruoli r 
                  INNER JOIN utenti u ON r.id_ruolo = u.id_ruolo 
                  WHERE u.email = ? AND u.id_tenant = ?";
    try {
        $stmt_ruolo = $pdo->prepare($sql_ruolo);
        $stmt_ruolo->execute([$email, $token_tenant_id]);
        $ruolo = $stmt_ruolo->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $ruolo = null;
    }

    // Query per ottenere i privilegi dell'utente basati sul ruolo
    $privilegi = [];

    if ($ruolo) {
        $sql_privilegi = "SELECT p.id_privilegio, p.nome_privilegio, p.descrizione 
                          FROM privilegi p 
                          INNER JOIN ruolo_privilegi rp ON p.id_privilegio = rp.id_privilegio 
                          WHERE rp.id_ruolo = ?";
        try {
            $stmt_privilegi = $pdo->prepare($sql_privilegi);
            $stmt_privilegi->execute([$ruolo['id_ruolo']]);
            $privilegi = $stmt_privilegi->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $privilegi = [];
        }
    }
    
    // Risposta di successo
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'user' => $user,
        'tenant_id' => $token_tenant_id,
        'role' => $ruolo,
        'permissions' => $privilegi
    ]);
    
} catch (\Firebase\JWT\ExpiredException $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Token scaduto']);
} catch (\Firebase\JWT\SignatureInvalidException $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Token non valido']);
} catch (\Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Token non valido: ' . $e->getMessage()]);
}

$pdo = null;
?>
