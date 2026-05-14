<?php
/**
 * Helper centralizzato per il contesto tenant.
 */

function musicare_normalize_tenant_id($value): ?string
{
    if (!is_string($value)) {
        return null;
    }

    $value = trim($value);
    if ($value === '') {
        return null;
    }

    if (!preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $value)) {
        return null;
    }

    return $value;
}

function musicare_get_default_tenant_id(): string
{
    $fromEnv = getenv('MUSICARE_DEFAULT_TENANT');
    $normalized = musicare_normalize_tenant_id($fromEnv);
    return $normalized ?? 'public';
}

function musicare_get_request_tenant_id(): ?string
{
    if (!empty($_SERVER['HTTP_X_TENANT_ID'])) {
        return musicare_normalize_tenant_id($_SERVER['HTTP_X_TENANT_ID']);
    }

    if (!empty($_GET['tenant'])) {
        return musicare_normalize_tenant_id((string)$_GET['tenant']);
    }

    if (!empty($_POST['tenant'])) {
        return musicare_normalize_tenant_id((string)$_POST['tenant']);
    }

    return null;
}

function musicare_get_current_tenant_id(bool $includeSession = true): string
{
    if ($includeSession && isset($_SESSION['tenant_id'])) {
        $sessionTenant = musicare_normalize_tenant_id((string)$_SESSION['tenant_id']);
        if ($sessionTenant !== null) {
            return $sessionTenant;
        }
    }

    $requestTenant = musicare_get_request_tenant_id();
    if ($requestTenant !== null) {
        return $requestTenant;
    }

    return musicare_get_default_tenant_id();
}

function musicare_set_session_tenant_id(string $tenantId): void
{
    $normalized = musicare_normalize_tenant_id($tenantId);
    if ($normalized === null) {
        throw new InvalidArgumentException('tenant_id non valido');
    }

    $_SESSION['tenant_id'] = $normalized;
}
