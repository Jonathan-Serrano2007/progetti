<?php
use Firebase\JWT\JWT;

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
