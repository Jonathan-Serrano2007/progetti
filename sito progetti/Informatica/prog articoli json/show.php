<?php
// show.php: mostra tutti gli articoli salvati in articoli.json
$file_json = __DIR__ . '/articoli.json';
$articoli = file_exists($file_json) ? json_decode(file_get_contents($file_json), true) : [];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Articoli Salvati</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card-body {
            min-height: 140px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }
    </style>
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">Articoli Salvati</h2>
    <div class="row">
        <?php if (empty($articoli)) { ?>
            <p>Nessun articolo presente.</p>
        <?php } else {
            foreach ($articoli as $index => $articolo) { ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="immagini/<?php echo htmlspecialchars($articolo['immagine']); ?>" class="card-img-top" alt="immagine articolo">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($articolo['titolo']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($articolo['descrizione']); ?></p>
                            <p class="card-text"><strong>Prezzo: € <?php echo number_format($articolo['prezzo'], 2, ',', '.'); ?></strong></p>
                            <form method="POST" action="elimina.php" class="mt-2">
                                <input type="hidden" name="index" value="<?php echo $index; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Elimina</button>
                            </form>
                        </div>
                    </div>
                </div>
        <?php } } ?>
    </div>
    <a href="index.php" class="btn btn-secondary mt-4">Torna alla pagina di inserimento</a>
</div>
</body>
</html>
