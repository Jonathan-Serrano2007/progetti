<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Inserisci Articolo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">Inserisci un nuovo articolo</h2>
    <form action="insert.php" method="POST">
        <div class="mb-3">
            <label for="titolo" class="form-label">Titolo</label>
            <input type="text" class="form-control" id="titolo" name="titolo" required>
        </div>
        <div class="mb-3">
            <label for="descrizione" class="form-label">Descrizione</label>
            <textarea class="form-control" id="descrizione" name="descrizione" rows="3" required></textarea>
        </div>
        <div class="mb-3">
            <label for="prezzo" class="form-label">Prezzo (€)</label>
            <input type="number" step="0.01" class="form-control" id="prezzo" name="prezzo" required>
        </div>
        <div class="mb-3">
            <label for="immagine" class="form-label">Immagine</label>
            <select class="form-select" id="immagine" name="immagine" required>
                <option value="immagine1.jpg">Articolo 1</option>
                <option value="immagine2.jpg">Articolo 2</option>
                <option value="immagine3.jpg">Articolo 3</option>
                <option value="immagine4.jpg">Articolo 4</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Inserisci</button>
        <a href="show.php" class="btn btn-success ms-2">Visualizza Articoli</a>
    </form>
    <div class="mt-3">
        <a href="../INFORMATICA.php" class="btn btn-secondary">Torna indietro</a>
        
    </div>
</div>
</body>
</html>
