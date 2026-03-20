<?php
/**
 * esercizi_example.php
 * 
 * ESEMPIO DI UTILIZZO DEL SISTEMA DI PRIVILEGI
 * 
 * Questo file mostra come implementare il controllo dei privilegi
 * nelle pagine dell'applicazione Musicare
 */

session_start();

// Includi il sistema di database e privilegi
require_once 'database.php';
require_once 'check_privileges.php';

// =========================================================================
// ESEMPIO 1: Richiedi autenticazione
// =========================================================================
require_login('login.php');

// =========================================================================
// ESEMPIO 2: Richiedi privilegio specifico per accedere agli esercizi base
// =========================================================================
require_privilege('svolge_esercizi_base', 'index.php');

// A questo punto, l'utente è certamente loggato E ha il privilegio

// Leggi il numero dell'esercizio
$esercizio_num = intval($_GET['esercizio'] ?? 0);

if ($esercizio_num < 1 || $esercizio_num > 10) {
    die("Numero esercizio non valido");
}

// =========================================================================
// ESEMPIO 3: Controllo dinamico di privilegi
// =========================================================================

$user_role = get_user_role();
$user_id = get_user_id();
$user_name = get_user_name();

// Se è esercizio avanzato (6-10), verifica il privilegio Pro
if ($esercizio_num >= 6) {
    require_privilege('svolge_esercizi_pro', 'index.php');
}

// =========================================================================
// ESEMPIO 4: Condizioni di visualizzazione
// =========================================================================
$can_save_progress = check_privilege('salva_progressi');
$can_see_graphics = check_privilege('visualizza_grafici');
$is_pro_user = is_pro();

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Esercizio <?php echo $esercizio_num; ?> - Musicare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="musicare-theme.css" rel="stylesheet">
</head>
<body class="bg-dark text-white">

<nav class="navbar navbar-dark bg-darker">
    <div class="container-fluid">
        <span class="navbar-brand">Musicare - Esercizio <?php echo $esercizio_num; ?></span>
        <div>
            <span>Ciao, <?php echo htmlspecialchars($user_name); ?> (<?php echo htmlspecialchars($user_role); ?>)</span>
            <a href="index.php?logout=1" class="btn btn-sm btn-danger ms-2">Esci</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    
    <!-- INFORMAZIONI UTENTE -->
    <div class="alert alert-info">
        <strong>ID Utente:</strong> <?php echo $user_id; ?><br>
        <strong>Ruolo:</strong> <?php echo htmlspecialchars($user_role); ?><br>
        <strong>Privilegi:</strong> <?php echo implode(', ', get_user_privileges()); ?>
    </div>

    <!-- ESERCIZIO -->
    <div class="card">
        <div class="card-header">
            <h4>Esercizio <?php echo $esercizio_num; ?></h4>
        </div>
        <div class="card-body">
            <p>Contenuto dell'esercizio qui...</p>
            
            <!-- TIMER E PUNTEGGIO -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card bg-secondary">
                        <div class="card-body text-center">
                            <h5>Timer</h5>
                            <div id="timer">00:00</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-secondary">
                        <div class="card-body text-center">
                            <h5>Punteggio</h5>
                            <div id="score">0/100</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- OPZIONI DI SALVATAGGIO -->
    <?php if ($can_save_progress): ?>
        <div class="alert alert-success mt-3">
            ✓ Puoi salvare i progressi di questo esercizio nel tuo profilo
        </div>
        <form method="POST" action="save_progress.php" class="mt-3">
            <input type="hidden" name="esercizio" value="<?php echo $esercizio_num; ?>">
            <input type="hidden" name="punteggio" id="final_score" value="0">
            <button type="submit" class="btn btn-success">Salva Progresso</button>
        </form>
    <?php else: ?>
        <div class="alert alert-warning mt-3">
            ⚠ I tuoi risultati non verranno salvati. 
            <a href="#">Abbiamo un piano Pro per te!</a>
        </div>
        <button type="button" class="btn btn-primary mt-3" disabled>Salva Progresso (disponibile solo per Pro)</button>
    <?php endif; ?>

</div>

<script>
    // Esempio di timer semplice
    let seconds = 0;
    let timerInterval = setInterval(() => {
        seconds++;
        let mins = Math.floor(seconds / 60);
        let secs = seconds % 60;
        document.getElementById('timer').innerText = 
            String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
    }, 1000);

    // Al termine dell'esercizio, salva il punteggio
    function finishExercise(score) {
        clearInterval(timerInterval);
        document.getElementById('final_score').value = score;
        document.getElementById('score').innerText = score + '/100';
    }
</script>

</body>
</html>
