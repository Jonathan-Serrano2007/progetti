<?php
/**
 * check_privileges.php
 * Sistema di controllo privilegi basato su ruoli e tabella "privilegi"
 * 
 * Utilizzo:
 * - session_start();
 * - require_once 'check_privileges.php';
 * - check_privilege('svolge_esercizi_pro'); // Ritorna true/false
 * - require_privilege('svolge_esercizi_pro'); // Esce se non ha privilegi
 */

require_once 'database.php';
require_once 'tenant_context.php';

/**
 * Ottieni i privilegi dell'utente loggato
 * @return array Lista di privilegi dell'utente
 */
function get_user_privileges() {
    global $pdo;
    $tenant_id = musicare_get_current_tenant_id();
    
    // Se l'utente non è loggato, ritorna array vuoto
    if (!isset($_SESSION['utente_id']) || !isset($_SESSION['utente_ruolo'])) {
        return [];
    }
    
    $ruolo = $_SESSION['utente_ruolo'];
    
    // Query per ottenere i privilegi del ruolo
    $sql = "
        SELECT p.nome_privilegio 
        FROM privilegi p
        INNER JOIN ruolo_privilegi rp ON p.id_privilegio = rp.id_privilegio
        INNER JOIN ruoli r ON rp.id_ruolo = r.id_ruolo
        INNER JOIN utenti u ON u.id_ruolo = r.id_ruolo
        WHERE r.nome_ruolo = ? AND u.id_utente = ? AND u.id_tenant = ?
    ";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ruolo, $_SESSION['utente_id'], $tenant_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $privilegi = [];
        foreach ($rows as $row) {
            $privilegi[] = $row['nome_privilegio'];
        }
        return $privilegi;
    } catch (PDOException $e) {
        error_log("Errore DB get_user_privileges: " . $e->getMessage());
        return [];
    }
}

/**
 * Verifica se l'utente ha un determinato privilegio
 * @param string $privilege Nome del privilegio da verificare
 * @return bool true se ha il privilegio, false altrimenti
 */
function check_privilege($privilege) {
    $privilegi = get_user_privileges();
    return in_array($privilege, $privilegi);
}

/**
 * Richiedi un privilegio, altrimenti reindirizza
 * @param string $privilege Nome del privilegio richiesto
 * @param string $redirect_url URL di reindirizzamento in caso di accesso negato
 */
function require_privilege($privilege, $redirect_url = 'index.php') {
    if (!check_privilege($privilege)) {
        // Log dell'accesso negato
        error_log("Accesso negato: Utente " . ($_SESSION['utente_id'] ?? 'anonimo') . " ha tentato di accedere a '$privilege'");
        
        // Reindirizza con messaggio d'errore
        $_SESSION['errore_accesso'] = "Non hai i permessi per accedere a questa risorsa.";
        header("Location: $redirect_url");
        exit;
    }
}

/**
 * Verifica se l'utente è loggato
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['utente_id']) && isset($_SESSION['utente_ruolo']);
}

/**
 * Richiedi accesso loggato
 * @param string $redirect_url URL per il login
 */
function require_login($redirect_url = 'login.php') {
    if (!is_logged_in()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header("Location: $redirect_url");
        exit;
    }
}

/**
 * Ottieni il nome del ruolo dell'utente
 * @return string
 */
function get_user_role() {
    return $_SESSION['utente_ruolo'] ?? 'guest';
}

/**
 * Ottieni l'ID dell'utente
 * @return int
 */
function get_user_id() {
    return $_SESSION['utente_id'] ?? null;
}

/**
 * Ottieni il nome dell'utente
 * @return string
 */
function get_user_name() {
    return $_SESSION['utente_nome'] ?? 'Ospite';
}

/**
 * Verifica se è un utente Pro
 * @return bool
 */
function is_pro() {
    $role = get_user_role();
    return $role === 'pro' || $role === 'admin';
}

/**
 * Verifica se è un admin
 * @return bool
 */
function is_admin() {
    return get_user_role() === 'admin';
}

/**
 * Log di azioni importanti
 * @param string $azione Descrizione dell'azione
 * @param array $dettagli Dettagli aggiuntivi (opzionale)
 */
function log_action($azione, $dettagli = []) {
    global $pdo;
    
    if (!is_logged_in()) {
        return;
    }
    
    $user_id = get_user_id();
    $timestamp = date('Y-m-d H:i:s');
    $dettagli_json = json_encode($dettagli);
    
    // Opzionale: se hai una tabella di log, inserisci qui
    // $sql = "INSERT INTO log_azioni (id_utente, azione, dettagli, timestamp) VALUES (?, ?, ?, ?)";
    // $stmt = $mysqli->prepare($sql);
    // $stmt->bind_param("isss", $user_id, $azione, $dettagli_json, $timestamp);
    // $stmt->execute();
    // $stmt->close();
    
    error_log("[$timestamp] User $user_id: $azione - " . json_encode($dettagli));
}

?>
