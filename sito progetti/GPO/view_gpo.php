<?php
// Minimal viewer: prende ?img=Nome%20esercizio e cerca in ./img/
$nameRaw = $_GET['img'] ?? '';
$nameRaw = trim($nameRaw);
$nameRaw = str_replace("\0", '', $nameRaw); // basic sanitize

$imgDir = __DIR__ . '/img/';
$found = '';
$tries = [];
$exts = ['png','jpg','jpeg','webp','gif','svg'];

if ($nameRaw !== '') {
	// costruisci possibili varianti del nome (per gestire "E/R" ecc.)
	$candidates = [
		$nameRaw,
		str_replace('/', '_', $nameRaw),
		str_replace('/', '-', $nameRaw),
		str_replace('/', '', $nameRaw),
		str_replace('%20', ' ', $nameRaw),
	];

	// rimuovi duplicati
	$candidates = array_values(array_unique($candidates));

	foreach ($candidates as $base) {
		foreach ($exts as $e) {
			$try = $imgDir . $base . '.' . $e;
			$tries[] = $try;
			if (file_exists($try) && is_file($try)) {
				$found = $try;
				$webPath = 'img/' . basename($try);
				break 2;
			}
		}
	}
}
?>
<!doctype html>
<html lang="it">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Visualizzatore - <?php echo htmlspecialchars($nameRaw ?: 'Immagine'); ?></title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
	<style> img { max-width:100%; height:auto; } .viewer { max-width:900px; margin:0 auto; } </style>
</head>
<body>
	<nav class="navbar navbar-light bg-light">
		<div class="container">
			<a class="navbar-brand" href="GPO.php">GPO</a>
		</div>
	</nav>

	<main class="container py-4">
		<div class="viewer text-center">
			<h3><?php echo htmlspecialchars($nameRaw ?: 'Immagine'); ?></h3>

			<?php if ($found): ?>
				<p class="text-muted">Immagine trovata: <?php echo htmlspecialchars(basename($found)); ?></p>
				<img src="<?php echo htmlspecialchars($webPath); ?>" alt="<?php echo htmlspecialchars($nameRaw); ?>" class="img-fluid rounded shadow-sm mb-3" />
			<?php else: ?>
				<div class="alert alert-warning">Immagine non trovata.<br>Ho provato queste possibili posizioni:
					<ul class="mt-2">
						<?php foreach ($tries as $t): ?>
							<li><small><?php echo htmlspecialchars($t); ?></small></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<div class="d-flex justify-content-center gap-2">
				<a href="GPO.php" class="btn btn-primary">Chiudi</a>
			</div>
		</div>
	</main>

	<footer class="text-center py-3">
		<small>Serranò Jonathan</small>
	</footer>

	<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
</body>
</html>