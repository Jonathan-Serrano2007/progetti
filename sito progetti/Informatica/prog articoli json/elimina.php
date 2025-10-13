<?php
// elimina.php: elimina un articolo dal file articoli.json
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['index'])) {
    $file_json = __DIR__ . '/articoli.json';
    $articoli = file_exists($file_json) ? json_decode(file_get_contents($file_json), true) : [];
    $index = intval($_POST['index']);
    if (isset($articoli[$index])) {
        array_splice($articoli, $index, 1);
        file_put_contents($file_json, json_encode($articoli, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
header('Location: show.php');
exit;
