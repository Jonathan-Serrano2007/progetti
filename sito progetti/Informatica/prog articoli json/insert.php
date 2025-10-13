<?php
require_once("articolo.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titolo = $_POST['titolo'] ?? '';
    $descrizione = $_POST['descrizione'] ?? '';
    $prezzo = $_POST['prezzo'] ?? 0;
    $immagine = $_POST['immagine'] ?? '';

    $articolo = new Articolo($titolo, $descrizione, $prezzo, $immagine);

    // Salva l'articolo nel file JSON
    $file_json = __DIR__ . '/articoli.json';
    $nuovo_articolo = [
        'titolo' => $titolo,
        'descrizione' => $descrizione,
        'prezzo' => $prezzo,
        'immagine' => $immagine
    ];
    if (file_exists($file_json)) {
        $articoli = json_decode(file_get_contents($file_json), true);
        if (!is_array($articoli)) $articoli = [];
    } else {
        $articoli = [];
    }
    $articoli[] = $nuovo_articolo;
    file_put_contents($file_json, json_encode($articoli, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Articolo Inserito</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">Articolo Creato</h2>
    <?php
        if (isset($articolo)) {
            $articolo->show();
        } else {
            echo "<p>Nessun articolo creato.</p>";
        }
    ?>
    <div class="mt-4">
        <a href="index.php" class="btn btn-secondary">Torna indietro</a>
    </div>
</div>
</body>
</html>
