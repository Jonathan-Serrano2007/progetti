<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        .home-btn-bottom {
            position: fixed;
            left: 20px;
            bottom: 20px;
            z-index: 1000;
        }
        body {
            background: linear-gradient(120deg, #e0eafc 0%, #cfdef3 100%);
            min-height: 100vh;
        }
        .project-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .project-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2d3a4b;
        }
        .project-desc {
            font-size: 1.1rem;
            color: #4b5d6b;
        }
        .codespace-btn {
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 0.5rem 1.5rem;
            font-size: 1rem;
            transition: background 0.2s;
            margin-top: 16px;
        }
        .codespace-btn:hover {
            background: #1e40af;
        }
    </style>
</head>
<body>
    <main>
        <div class="row justify-content-center">
            <h2 class="text-center">INFORMATICA</h2>
        </div>
        <div class="home-btn-bottom">
                <a href="../index.php" class="codespace-btn">Home</a>
        </div>
        <div class="container mt-4">
            <div class="project-card">
                <div class="project-title">Progetto personale</div>
                <a href="./musicare/login.php" class="codespace-btn">Vai al progetto</a>
            </div>
            <div class="project-card">
                <div class="project-title">Progetto 2: Gestione Articoli JSON</div>
                <div class="project-desc">Un'applicazione PHP per la gestione di articoli tramite file JSON.</div>
                    <a href="prog articoli json/index.php" class="codespace-btn">Vai al progetto</a>
            </div>
        </div>
    </main>
</body>
</html>