<?php
session_start();
require_once 'database.php';
require_once 'tenant_context.php';

$tenant_id = musicare_get_current_tenant_id();

// Verifica login
if (!isset($_SESSION['utente_id'])) {
    header('Location: login.php');
    exit;
}

$id_utente = $_SESSION['utente_id'];

// Recupera ruolo utente
$sql = "SELECT u.id_utente, u.nome, u.cognome, u.email, u.id_ruolo, r.nome_ruolo
        FROM utenti u
        LEFT JOIN ruoli r ON u.id_ruolo = r.id_ruolo
        WHERE u.id_utente = ? AND u.id_tenant = ?";
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_utente, $tenant_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    header('Location: login.php');
    exit;
}

$userRole = $user['nome_ruolo'] ?? 'registrato';

// Controlla se l'utente ha un abbonamento attivo
$sql_ab = "SELECT COUNT(*) as cnt FROM abbonamenti WHERE id_utente = ? AND id_tenant = ? AND data_inizio <= CURDATE() AND data_scadenza >= CURDATE()";
try {
    $stmt_ab = $pdo->prepare($sql_ab);
    $stmt_ab->execute([$id_utente, $tenant_id]);
    $res_ab = $stmt_ab->fetch(PDO::FETCH_ASSOC);
    $hasActiveSubscription = ($res_ab['cnt'] > 0);
} catch (PDOException $e) {
    $hasActiveSubscription = false;
}

// Categorie (titolo + descrizione breve)
$categories = [
    'dettato_ritmico' => [ 'title' => 'Dettato ritmico', 'desc' => 'Esercizi ritmici: trascrizione di sequenze ritmiche, battiti e accenti.' ],
    'ear_training' => [ 'title' => 'Ear training', 'desc' => 'Riconoscimento di intervalli, accordi, scale e altezze.' ],
    'riconoscimento_note' => [ 'title' => 'Riconoscimento delle note', 'desc' => 'Individuazione delle note sul pentagramma.' ],
    'tonalita' => [ 'title' => 'Tonalità', 'desc' => 'Riconoscimento della tonalità tramite alterazioni in chiave.' ],
];

// Recupera categoria richiesta
$cat = isset($_GET['cat']) ? $_GET['cat'] : null;
if (!$cat || !isset($categories[$cat])) {
    header('Location: categoria_esercizi.php');
    exit;
}

$categoryTitle = $categories[$cat]['title'];

// Crea array di 10 esercizi per la categoria
$exercises = [];
for ($i = 1; $i <= 10; $i++) {
    $exercises[] = [
        'id' => $i,
        'title' => $categoryTitle . " - Esercizio $i",
        'difficulty' => $i,
    ];
}

// Session-based completamento temporaneo (utilizzato perché gli esercizi sono vuoti)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$completed = [];
if (isset($_SESSION['completed']) && is_array($_SESSION['completed'])) {
    $completed = $_SESSION['completed'][$cat] ?? [];
}

function difficultyToStars($difficulty) {
    // Map difficoltà 1..10 a 0.5..5.0 (step di 0.5)
    $stars = round(($difficulty * 0.5) * 2) / 2;
    return $stars;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista Esercizi - Musicare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; min-height: 100vh; }
        .container { padding-top: 56px; padding-bottom: 48px; }
        .section-title { font-size: 1.6rem; font-weight: 700; margin-bottom: 6px; display:flex; align-items:center; gap:10px; }
        .section-desc { color: rgba(255,255,255,0.85); margin-bottom: 18px; }
        .exercise-card { background: rgba(255,255,255,0.04); border-radius: 14px; padding: 18px; margin-bottom: 18px; border:1px solid rgba(255,255,255,0.06); box-shadow: 0 6px 18px rgba(0,0,0,0.12); transition: transform .15s ease, box-shadow .15s ease; }
        .exercise-card:hover { transform: translateY(-6px); box-shadow: 0 12px 30px rgba(0,0,0,0.18); }
        .star { color: rgba(255,255,255,0.35); font-size: 1.15rem; margin-right: 6px; }
        .star.filled { color: #ffd400; }
        .star.half { color: #ffd400; position: relative; }
        .locked { opacity: 0.6; }
        .lock-badge { background: #f5576c; color: white; padding: 6px 12px; border-radius: 22px; font-weight:600; }
        .lock-overlay { position: absolute; inset: 0; display:flex; align-items:center; justify-content:center; background: rgba(0,0,0,0.45); border-radius: 14px; }
        .exercise-row { position: relative; }
    </style>
    <link href="musicare-theme.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <div class="section-title"><i class="bi bi-list-ul"></i> <?php echo htmlspecialchars($categoryTitle); ?> <small class="text-white-50 ms-2">Categoria</small></div>
                <div class="section-desc"><?php echo htmlspecialchars($categories[$cat]['desc']); ?></div>
            </div>
            <div>
                <span class="me-2">Utente: <strong><?php echo htmlspecialchars($user['nome']); ?></strong></span>
                <a href="categoria_esercizi.php" class="btn btn-sm btn-light">Torna alle categorie</a>
            </div>
        </div>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($_SESSION['flash_error']); ?>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <p class="mb-4">Troverai 10 esercizi con difficoltà crescente. I primi 5 sono disponibili a tutti; gli ultimi 5 richiedono abbonamento Pro attivo per essere svolti.</p>

        <?php foreach ($exercises as $ex):
            $stars = difficultyToStars($ex['difficulty']);
            // Controllo permessi (Pro vs Base) — preservo il controllo di piano separatamente
            $canAccessByPlan = ($ex['id'] <= 5) || ($userRole === 'admin') || ($userRole === 'pro' && $hasActiveSubscription);

            // Controllo sequenziale: l'esercizio N è sbloccabile solo se N-1 è segnato come svolto
            $prevCompleted = true;
            if ($ex['id'] > 1) {
                $prevCompleted = !empty($completed[$ex['id'] - 1]);
            }
            $isSequentialUnlocked = $prevCompleted;

            // Accessibile solo se sia il piano lo permette sia la sequenza è sbloccata
            $isClickable = $canAccessByPlan && $isSequentialUnlocked;
        ?>
            <?php $accents = ['dettato_ritmico'=>'#f6a560','ear_training'=>'#7fd3a6','riconoscimento_note'=>'#9f7aea','tonalita'=>'#4facfe']; $accent = isset($accents[$cat]) ? $accents[$cat] : '#4facfe'; ?>
            <div class="exercise-card d-flex justify-content-between align-items-center <?php echo $isClickable ? '' : 'locked'; ?> exercise-row" style="border-left: 4px solid <?php echo $accent; ?>;">
                <?php if (!$isClickable): ?>
                    <?php if (!$canAccessByPlan): ?>
                        <div class="lock-overlay"><div><span class="lock-badge">🔒 Pro</span></div></div>
                    <?php else: ?>
                        <div class="lock-overlay"><div><span class="lock-badge" style="background:#6c757d">🔒 Bloccato</span></div></div>
                    <?php endif; ?>
                <?php endif; ?>
                <div>
                    <h5 class="mb-1"><?php echo htmlspecialchars($ex['title']); ?> <small class="text-white-50">(Difficoltà: <?php echo $ex['difficulty']; ?>)</small></h5>
                    <div>
                        <?php for ($s = 1; $s <= 5; $s++):
                            if ($stars >= $s): ?>
                                <i class="bi bi-star-fill star filled"></i>
                            <?php elseif ($stars >= ($s - 0.5)): ?>
                                <i class="bi bi-star-half star half"></i>
                            <?php else: ?>
                                <i class="bi bi-star star"></i>
                            <?php endif;
                        endfor; ?>
                    </div>
                </div>
                <div>
                    <?php if ($isClickable): ?>
                        <a href="esercizio.php?id=<?php echo $ex['id']; ?>&cat=<?php echo urlencode($cat); ?>" class="btn btn-sm btn-primary start-btn" data-id="<?php echo $ex['id']; ?>">Inizia</a>
                        <?php if (empty($completed[$ex['id']])): ?>
                            <button class="btn btn-sm btn-success mark-done-btn" data-id="<?php echo $ex['id']; ?>" data-cat="<?php echo htmlspecialchars($cat); ?>">Segna come svolto</button>
                        <?php else: ?>
                            <span class="badge bg-success">Svolto</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php if (($ex['id'] > 5) && !($userRole === 'admin' || ($userRole === 'pro' && $hasActiveSubscription))): ?>
                            <a href="register.php" class="btn btn-sm btn-outline-light">Iscriviti Pro</a>
                        <?php else: ?>
                            <button class="btn btn-sm btn-secondary" disabled>Bloccato</button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="mt-4">
            <small class="text-white-50">Nota: la rappresentazione delle stelle è proporzionale alla difficoltà (1 più facile → 10 più difficile). Le mezze stelline indicano livelli intermedi.</small>
        </div>
    </div>

    <script>
        async function markDone(id, cat, btn) {
            try {
                const form = new FormData();
                form.append('id', id);
                form.append('cat', cat);

                const res = await fetch('mark_done.php', {
                    method: 'POST',
                    body: form
                });
                const data = await res.json();
                if (data.ok) {
                    // aggiorna UI: mostra badge svolto e abilita il prossimo esercizio
                    const parent = btn.parentElement;
                    btn.remove();
                    const span = document.createElement('span');
                    span.className = 'badge bg-success';
                    span.innerText = 'Svolto';
                    parent.appendChild(span);

                    const nextId = parseInt(id) + 1;
                    const nextStart = document.querySelector('.start-btn[data-id="' + nextId + '"]');
                    if (nextStart) {
                        const nextCard = nextStart.closest('.exercise-card');
                        if (nextCard) {
                            nextCard.classList.remove('locked');
                            const overlay = nextCard.querySelector('.lock-overlay');
                            if (overlay) overlay.remove();
                            const disabledBtn = nextCard.querySelector('button[disabled]');
                            if (disabledBtn) disabledBtn.remove();
                        }
                    }
                } else {
                    alert('Errore: ' + (data.error || 'impossibile segnare come svolto'));
                }
            } catch (e) {
                console.error(e);
                alert('Errore di rete');
            }
        }

        document.addEventListener('click', function(e) {
            if (e.target && e.target.matches('.mark-done-btn')) {
                const id = e.target.getAttribute('data-id');
                const cat = e.target.getAttribute('data-cat');
                markDone(id, cat, e.target);
            }
        });
    </script>
</body>
</html>