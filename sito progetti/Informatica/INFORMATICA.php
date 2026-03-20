<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informatica - Progetti</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: radial-gradient(circle at 20% 20%, rgba(29, 185, 84, 0.2) 0%, transparent 35%),
                        linear-gradient(135deg, #0f0f0f 0%, #171717 100%);
            color: #fff;
        }

        .page-wrap {
            width: min(1040px, 92vw);
            margin: 0 auto;
            padding: 3rem 0 4rem;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        h1 {
            margin: 0;
            font-size: clamp(1.8rem, 4vw, 2.6rem);
            font-weight: 700;
        }

        .subtitle {
            margin: 0.5rem 0 0;
            color: #b3b3b3;
            font-weight: 300;
        }

        .home-btn {
            border-radius: 999px;
            background: transparent;
            color: #fff;
            border: 2px solid rgba(255, 255, 255, 0.55);
            padding: 0.65rem 1.4rem;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.25s ease;
        }

        .home-btn:hover {
            background: #fff;
            color: #0f0f0f;
        }

        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.25rem;
        }

        .project-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.22);
            padding: 2rem;
            display: flex;
            flex-direction: column;
            min-height: 260px;
            transition: transform 0.25s ease, border-color 0.25s ease;
        }

        .project-card:hover {
            transform: translateY(-6px);
            border-color: #1db954;
        }

        .project-badge {
            display: inline-flex;
            align-items: center;
            width: max-content;
            border-radius: 999px;
            background: rgba(29, 185, 84, 0.18);
            color: #7effac;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            padding: 0.35rem 0.8rem;
            margin-bottom: 0.9rem;
        }

        .project-title {
            font-size: 1.35rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 0.6rem;
        }

        .project-desc {
            font-size: 1rem;
            color: #c9c9c9;
            line-height: 1.5;
            margin: 0 0 1.2rem;
        }

        .codespace-btn {
            margin-top: auto;
            width: fit-content;
            background: #1db954;
            color: #fff;
            border: none;
            border-radius: 999px;
            padding: 0.62rem 1.4rem;
            font-size: 0.95rem;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.25s ease;
        }

        .codespace-btn:hover {
            background: #1ed760;
            color: #fff;
            transform: scale(1.03);
        }

        @media (max-width: 700px) {
            .page-wrap {
                padding-top: 2rem;
            }
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
    </style>
</head>
<body>
    <main class="page-wrap">
        <div class="header">
            <div>
                <h1><i class="bi bi-cpu"></i> Informatica</h1>
                <p class="subtitle">Seleziona un progetto e accedi direttamente alla sua pagina principale.</p>
            </div>
            <a href="../index.php" class="home-btn"><i class="bi bi-house"></i> Home</a>
        </div>

        <section class="projects-grid" aria-label="Elenco progetti informatica">
            <div class="project-card">
                <div class="project-badge">MUSICARE</div>
                <div class="project-title">Progetto personale</div>
                <p class="project-desc">Piattaforma musicale con accesso, registrazione e dashboard utente.</p>
                <a href="./musicare/index.php" class="codespace-btn">Vai al progetto</a>
            </div>

            <div class="project-card">
                <div class="project-badge">JSON APP</div>
                <div class="project-title">Gestione Articoli JSON</div>
                <p class="project-desc">Applicazione PHP per inserimento e visualizzazione articoli da file JSON.</p>
                <a href="prog articoli json/index.php" class="codespace-btn">Vai al progetto</a>
            </div>
        </section>
    </main>
</body>
</html>