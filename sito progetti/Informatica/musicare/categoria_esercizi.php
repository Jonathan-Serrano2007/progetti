<?php
session_start();
require_once 'database.php';

// Verifica login
if (!isset($_SESSION['utente_id'])) {
    header('Location: login.php');
    exit;
}

$categories = [
    'dettato_ritmico' => [
        'title' => 'Dettato ritmico',
        'desc' => 'Esercizi in cui l’utente ascolta una sequenza ritmica composta da battiti, figure musicali e pause, e deve trascriverla correttamente. Questa categoria allena il riconoscimento delle durate, degli accenti e dei pattern ritmici, migliorando la precisione e la consapevolezza ritmica.'
    ],
    'ear_training' => [
        'title' => 'Ear training',
        'desc' => 'Esercizi pensati per sviluppare l’orecchio musicale e la capacità di riconoscere gli elementi sonori senza il supporto dello spartito. Comprendono attività di riconoscimento di intervalli, accordi, scale, progressioni armoniche e altezze delle note.'
    ],
    'riconoscimento_note' => [
        'title' => 'Riconoscimento delle note',
        'desc' => 'Esercizi in cui viene mostrata una nota sul pentagramma e l’utente deve identificarla correttamente (Do, Re, Mi… oppure C, D, E). Questa categoria è utile per migliorare la lettura a prima vista e rafforzare il collegamento tra notazione musicale e altezza del suono.'
    ],
    'tonalita' => [
        'title' => 'Tonalità',
        'desc' => 'Esercizi dedicati al riconoscimento della tonalità attraverso l’osservazione delle alterazioni in chiave (diesis o bemolli). Allenano l’utente a individuare rapidamente tonalità maggiori e minori.'
    ],
];

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorie Esercizi - Musicare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; min-height: 100vh; }
        .container { padding-top: 56px; padding-bottom: 48px; }
        .section-title { font-size: 1.6rem; font-weight: 700; margin-bottom: 6px; display: flex; align-items: center; gap: 10px; }
        .section-desc { color: rgba(255,255,255,0.85); margin-bottom: 18px; }
        .cat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 18px; }
        .cat-card { background: rgba(255,255,255,0.05); border-radius: 14px; padding: 22px; border-left: 6px solid rgba(255,255,255,0.06); box-shadow: 0 6px 18px rgba(0,0,0,0.12); transition: transform .18s ease, box-shadow .18s ease; }
        .cat-card:hover { transform: translateY(-6px); box-shadow: 0 12px 30px rgba(0,0,0,0.18); }
        .cat-icon { font-size: 1.6rem; margin-right: 12px; }
        .cat-title { font-size: 1.15rem; margin-bottom: 6px; }
        .cat-desc { color: rgba(255,255,255,0.75); margin-bottom: 10px; font-size: 0.95rem; }
        .btn-outline-light { border-color: rgba(255,255,255,0.15); }
    </style>
    <link href="musicare-theme.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Categorie Esercizi</h2>
            <div>
                <a href="dashboard.php" class="btn btn-sm btn-light">Torni alla Dashboard</a>
            </div>
        </div>

        <p class="mb-4">Scegli una categoria per visualizzare gli esercizi disponibili (10 per categoria).</p>

        <div class="cat-grid">
        <?php
            $icons = [
                'dettato_ritmico' => 'bi-music-note-list',
                'ear_training' => 'bi-ear',
                'riconoscimento_note' => 'bi-file-earmark-music',
                'tonalita' => 'bi-key'
            ];
            $colors = [
                'dettato_ritmico' => '#f6a560',
                'ear_training' => '#7fd3a6',
                'riconoscimento_note' => '#9f7aea',
                'tonalita' => '#4facfe'
            ];
        ?>
        <?php foreach ($categories as $slug => $cat): ?>
            <?php $icon = $icons[$slug] ?? 'bi-music-note'; $accent = $colors[$slug] ?? '#4facfe'; ?>
            <div class="cat-card" style="border-left-color: <?php echo $accent; ?>;">
                <div class="d-flex align-items-start">
                    <div class="cat-icon" style="color: <?php echo $accent; ?>;"><i class="bi <?php echo $icon; ?>"></i></div>
                    <div class="flex-grow-1">
                        <div class="cat-title"><?php echo htmlspecialchars($cat['title']); ?></div>
                        <div class="cat-desc"><?php echo htmlspecialchars($cat['desc']); ?></div>
                        <a href="elenco_esercizi.php?cat=<?php echo urlencode($slug); ?>" class="btn btn-outline-light btn-sm">Apri Categoria</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>

    </div>
</body>
</html>