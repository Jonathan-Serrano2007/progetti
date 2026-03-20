<?php

function getJwtSecret(): string
{
    $defaultSecret = 'musicare-local-dev-jwt-secret-key-2026-min-32-chars';
    $secret = getenv('JWT_SECRET') ?: $defaultSecret;

    if (strlen($secret) < 32) {
        throw new RuntimeException('JWT secret troppo corta: minimo 32 caratteri richiesti.');
    }

    return $secret;
}
