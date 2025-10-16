<!doctype html>
<html lang="en">
    <head>
        <title>Serranò Jonathan Progetti Scolastici 25/26</title>
        <!-- Required meta tags -->
        <meta charset="utf-8" />
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1, shrink-to-fit=no"
        />

        <!-- Bootstrap CSS v5.2.1 -->
        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
            rel="stylesheet"
            integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
            crossorigin="anonymous"
        />
        <style>
            body {
                background: linear-gradient(120deg, #e0eafc 0%, #cfdef3 100%);
                min-height: 100vh;
            }
            .project-card {
                background: #fff;
                border-radius: 12px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                padding: 2rem;
                margin: 2rem auto;
                max-width: 720px;
            }
            .project-title {
                font-size: 1.5rem;
                font-weight: bold;
                color: #2d3a4b;
            }
            .project-desc {
                font-size: 1.05rem;
                color: #4b5d6b;
                margin-top: .5rem;
            }
            .codespace-btn {
                background: #2563eb;
                color: #fff;
                border: none;
                border-radius: 8px;
                padding: 0.5rem 1.25rem;
                font-size: 1rem;
                transition: background 0.2s;
                margin-top: 16px;
            }
            .codespace-btn:hover { background: #1e40af; }
        </style>
    </head>

    <body>
        <header>
            <!-- place navbar here -->
        </header>
        <main>
            <div class="row justify-content-center">
                <h2 class="text-center mt-4">TEP</h2>
            </div>
            <div class="container">
                <div class="project-card">
                    <div class="project-title">API TEP</div>
                    <div class="project-desc">Esempio di API che mostra immagini casuali tramite TheCatAPI. Puoi aprire la piccola applicazione che usa cURL per recuperare immagini.</div>
                    <div class="mt-3">
                        <a href="API%20TEP/index.php" class="codespace-btn">Vai all'API TEP</a>
                        
                    </div>
                </div>
            </div>
        </main>
        <footer>
            <!-- place footer here -->
        </footer>
        <script>
            // fallback: se il link con spazio non funziona, proviamo a costruire l'URL correttamente
            document.addEventListener('DOMContentLoaded', function(){
                var btn = document.getElementById('tep-fallback');
                if(!btn) return;
                btn.addEventListener('click', function(e){
                    e.preventDefault();
                    var path = this.getAttribute('href');
                    // encodeURI gestisce gli spazi
                    window.location.href = encodeURI(path);
                });
            });
        </script>
        <!-- Bootstrap JavaScript Libraries -->
        <script
            src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
            integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
            crossorigin="anonymous"
        ></script>

        <script
            src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
            integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
            crossorigin="anonymous"
        ></script>
    </body>
</html>
