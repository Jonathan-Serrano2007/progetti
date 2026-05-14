<?php
session_start();
require_once 'database.php';
require_once 'tenant_context.php';

$tenant_id = musicare_get_current_tenant_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_progress') {
    header('Content-Type: application/json');

    if (!isset($_SESSION['utente_id'])) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'unauthorized']);
        exit;
    }

    $exerciseId = intval($_POST['exercise_id'] ?? 0);
    $result = intval($_POST['result'] ?? 0);
    $timeSpent = intval($_POST['time_spent'] ?? 0);

    if ($exerciseId < 1 || $exerciseId > 10) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'invalid_exercise']);
        exit;
    }

    $id_utente = $_SESSION['utente_id'];

    $stmt_save = $pdo->prepare("INSERT INTO svolge (id_utente, id_esercizio, tempo_impiegato, risultato, id_tenant) VALUES (?, ?, ?, ?, ?)");
    $stmt_save->execute([$id_utente, $exerciseId, $timeSpent, $result, $tenant_id]);

    $stmt_avg = $pdo->prepare("SELECT AVG(risultato) as avg_result, AVG(tempo_impiegato) as avg_time FROM svolge WHERE id_utente = ? AND id_tenant = ?");
    $stmt_avg->execute([$id_utente, $tenant_id]);
    $avg = $stmt_avg->fetch(PDO::FETCH_ASSOC);

    $media_punti = $avg && $avg['avg_result'] !== null ? round($avg['avg_result'], 2) : 0;
    $tempo_medio = $avg && $avg['avg_time'] !== null ? round($avg['avg_time'], 2) : 0;

    $stmt_check = $pdo->prepare("SELECT id_progresso FROM progressi WHERE id_utente = ? AND id_tenant = ?");
    $stmt_check->execute([$id_utente, $tenant_id]);
    $existing = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $stmt_update = $pdo->prepare("UPDATE progressi SET media_punti = ?, tempo_medio_impiegato = ? WHERE id_utente = ? AND id_tenant = ?");
        $stmt_update->execute([$media_punti, $tempo_medio, $id_utente, $tenant_id]);
    } else {
        $nextId = 1;
        $stmt_max = $pdo->prepare("SELECT MAX(id_progresso) as max_id FROM progressi WHERE id_tenant = ?");
        $stmt_max->execute([$tenant_id]);
        if ($stmt_max) {
            $row_max = $stmt_max->fetch(PDO::FETCH_ASSOC);
            if ($row_max && $row_max['max_id'] !== null) {
                $nextId = intval($row_max['max_id']) + 1;
            }
        }

        $stmt_insert = $pdo->prepare("INSERT INTO progressi (id_progresso, id_utente, media_punti, tempo_medio_impiegato, id_tenant) VALUES (?, ?, ?, ?, ?)");
        $stmt_insert->execute([$nextId, $id_utente, $media_punti, $tempo_medio, $tenant_id]);
    }

    echo json_encode(['ok' => true, 'media_punti' => $media_punti, 'tempo_medio' => $tempo_medio]);
    exit;
}

// Controllo login
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
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_utente, $tenant_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$userRole = $user['nome_ruolo'] ?? 'registrato';

// Controlla abbonamento attivo
$sql_ab = "SELECT COUNT(*) as cnt FROM abbonamenti WHERE id_utente = ? AND id_tenant = ? AND data_inizio <= CURDATE() AND data_scadenza >= CURDATE()";
$stmt_ab = $pdo->prepare($sql_ab);
$stmt_ab->execute([$id_utente, $tenant_id]);
$res_ab = $stmt_ab->fetch(PDO::FETCH_ASSOC);

$hasActiveSubscription = ($res_ab['cnt'] > 0);

// Validazione parametro id
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id < 1 || $id > 10) {
    header('Location: esercizi.php');
    exit;
}

// Se esercizio >5 serve abbonamento pro attivo (admin sempre ammesso)
if ($id > 5 && !($userRole === 'admin' || ($userRole === 'pro' && $hasActiveSubscription))) {
    // Non autorizzato
    $_SESSION['flash_error'] = 'Questo esercizio è riservato agli utenti Pro con abbonamento attivo.';
    header('Location: esercizi.php');
    exit;
}

// Mappa stelle (mezze stelline supportate)
function difficultyToStars($difficulty) {
    // Mappa difficoltà 1..10 a 0.5..5.0 (step di 0.5)
    $stars = round(($difficulty * 0.5) * 2) / 2; // assicura multipli di 0.5
    return $stars;
}

// Recupera categoria se presente
$cat = isset($_GET['cat']) ? $_GET['cat'] : null;
$categories = [
    'dettato_ritmico' => 'Dettato ritmico',
    'ear_training' => 'Ear training',
    'riconoscimento_note' => 'Riconoscimento delle note',
    'tonalita' => 'Tonalità',
];
$categoryTitle = isset($categories[$cat]) ? $categories[$cat] : null;

$difficulty = $id; // progressivo
$stars = difficultyToStars($difficulty);

function starsToNoteFolder($stars) {
    if (abs($stars - round($stars)) < 0.001) {
        return (string)intval(round($stars));
    }

    return number_format($stars, 1, ',', '');
}

$noteExerciseData = [
    'folder' => null,
    'items' => [],
    'buttons' => [],
];

if ($cat === 'riconoscimento_note') {
    $noteFolder = starsToNoteFolder($stars);
    $noteDir = __DIR__ . '/note selezionate/' . $noteFolder;
    $baseNotes = ['do', 're', 'mi', 'fa', 'sol', 'la', 'si'];
    $noteItems = [];

    if (is_dir($noteDir)) {
        $files = scandir($noteDir);
        foreach ($files as $file) {
            if (!preg_match('/\.(png|jpe?g|gif)$/i', $file)) {
                continue;
            }

            $noteKey = strtolower(pathinfo($file, PATHINFO_FILENAME));
            if (!preg_match('/^(do|re|mi|fa|sol|la|si)\d*$/', $noteKey)) {
                continue;
            }

            $baseLabel = preg_replace('/\d+$/', '', $noteKey);
            $noteItems[] = [
                'noteKey' => $baseLabel,
                'label' => $baseLabel,
                'src' => 'note selezionate/' . $noteFolder . '/' . $file,
            ];
        }
    }

    $availableKeys = [];
    foreach ($noteItems as $item) {
        $availableKeys[$item['label']] = true;
    }

    $buttonOrder = $baseNotes;
    $availableLabels = [];
    foreach ($noteItems as $item) {
        $availableLabels[$item['label']] = true;
    }

    $noteButtons = [];
    foreach ($buttonOrder as $label) {
        if (isset($availableLabels[$label])) {
            $noteButtons[] = [
                'noteKey' => $label,
                'label' => $label,
            ];
        }
    }

    $noteExerciseData = [
        'folder' => $noteFolder,
        'items' => $noteItems,
        'buttons' => $noteButtons,
    ];
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo "Esercizio $id"; ?> - Musicare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; min-height: 100vh; }
        .container { padding-top: 40px; }
        .star { color: rgba(255,255,255,0.35); }
        .star.filled { color: #ffd400; }
        .star.half { color: #ffd400; }
        .note-panel { text-align: center; }
        .note-controls { display: flex; justify-content: center; flex-wrap: wrap; gap: 12px; }
        .note-controls .btn { padding: 14px 28px; font-size: 1.1rem; min-width: 96px; }
        .note-stats { display: flex; justify-content: center; gap: 16px; flex-wrap: wrap; }
        .note-stat { background: rgba(102,126,234,0.12); border: 1px solid rgba(102,126,234,0.25); border-radius: 10px; padding: 10px 16px; min-width: 140px; }
        .note-stat strong { display: block; font-size: 1.1rem; }
    </style>
    <link href="musicare-theme.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><?php echo 'Esercizio ' . $id . ($categoryTitle ? ' - ' . htmlspecialchars($categoryTitle) : ''); ?></h2>
            <?php if ($categoryTitle): ?>
                <a href="elenco_esercizi.php?cat=<?php echo urlencode($cat); ?>" class="btn btn-sm btn-light">Torna alla lista</a>
            <?php else: ?>
                <a href="esercizi.php" class="btn btn-sm btn-light">Torna alla lista</a>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <strong>Difficoltà:</strong> <?php echo $difficulty; ?>
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

        <div class="card mb-4" style="background: rgba(255,255,255,0.06); border:none;">
            <div class="card-body text-dark" style="background: white; border-radius: 8px;">
                <?php if ($cat === 'riconoscimento_note'): ?>
                    <h5 class="card-title">Riconoscimento delle note</h5>
                    <?php if (empty($noteExerciseData['items'])): ?>
                        <p class="card-text">Nessuna nota trovata per la difficolta selezionata.</p>
                    <?php else: ?>
                        <p class="card-text">Guarda l'immagine e seleziona la nota corretta.</p>
                        <div class="note-stats mb-3">
                            <div class="note-stat">
                                <span>Tempo</span>
                                <strong id="note-timer">01:00</strong>
                            </div>
                            <div class="note-stat">
                                <span>Punteggio</span>
                                <strong id="note-score">0</strong>
                            </div>
                        </div>
                        <div class="note-panel mb-3">
                            <img id="note-image" src="" alt="Nota musicale" style="max-width: 320px; width: 100%; height: auto;">
                        </div>
                        <div id="note-feedback" class="mb-3 note-panel">Seleziona la nota corretta.</div>
                        <div class="note-controls">
                            <?php foreach ($noteExerciseData['buttons'] as $btn): ?>
                                <button type="button" class="btn btn-outline-primary" data-note="<?php echo htmlspecialchars($btn['noteKey']); ?>">
                                    <?php echo htmlspecialchars($btn['label']); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <h5 class="card-title">Dettagli Esercizio</h5>
                    <p class="card-text">Questo è un esempio di esercizio con difficoltà progressiva. Implementa qui il contenuto reale, il timer e la valutazione.</p>
                    <a href="#" class="btn btn-primary">Inizia Esercizio</a>
                <?php endif; ?>
            </div>
        </div>

    </div>
<?php if ($cat === 'riconoscimento_note' && !empty($noteExerciseData['items'])): ?>
<script>
    const noteItems = <?php echo json_encode($noteExerciseData['items'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    const noteImage = document.getElementById('note-image');
    const noteFeedback = document.getElementById('note-feedback');
    const noteButtons = document.querySelectorAll('[data-note]');
    const noteTimer = document.getElementById('note-timer');
    const noteScore = document.getElementById('note-score');
    let currentIndex = -1;
    let remainingSeconds = 60;
    let score = 0;
    let isActive = true;
    let timerId = null;

    function showRandomNote() {
        if (!noteItems.length || !isActive) {
            return;
        }

        let nextIndex = Math.floor(Math.random() * noteItems.length);
        if (noteItems.length > 1) {
            while (nextIndex === currentIndex) {
                nextIndex = Math.floor(Math.random() * noteItems.length);
            }
        }

        currentIndex = nextIndex;
        const item = noteItems[currentIndex];
        noteImage.src = encodeURI(item.src);
        noteFeedback.textContent = 'Seleziona la nota corretta.';
    }

    function updateTimer() {
        const mins = Math.floor(remainingSeconds / 60);
        const secs = remainingSeconds % 60;
        noteTimer.textContent = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
    }

    function stopExercise() {
        isActive = false;
        clearInterval(timerId);
        noteFeedback.textContent = 'Tempo scaduto. Punteggio finale: ' + score;
        noteButtons.forEach((button) => {
            button.disabled = true;
        });

        const payload = new URLSearchParams();
        payload.append('action', 'save_progress');
        payload.append('exercise_id', '<?php echo $id; ?>');
        payload.append('result', String(score));
        payload.append('time_spent', String(60 - remainingSeconds));

        fetch(window.location.href, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: payload.toString()
        }).catch(() => {
            noteFeedback.textContent = 'Tempo scaduto. Punteggio finale: ' + score + ' (salvataggio fallito)';
        });
    }

    function startTimer() {
        updateTimer();
        timerId = setInterval(() => {
            if (!isActive) {
                return;
            }

            remainingSeconds -= 1;
            updateTimer();
            if (remainingSeconds <= 0) {
                stopExercise();
            }
        }, 1000);
    }

    noteButtons.forEach((button) => {
        button.addEventListener('click', () => {
            if (!noteItems.length || currentIndex === -1 || !isActive) {
                return;
            }

            const guess = button.dataset.note;
            const target = noteItems[currentIndex];

            if (guess === target.label) {
                score += 100;
                noteScore.textContent = score;
                noteFeedback.textContent = 'Corretto!';
                setTimeout(showRandomNote, 50);
            } else {
                score = Math.max(0, score - 100);
                noteScore.textContent = score;
                noteFeedback.textContent = 'Riprova.';
            }
        });
    });

    showRandomNote();
    startTimer();
</script>
<?php endif; ?>
</body>
</html>