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
                background: linear-gradient(120deg, #f6d365 0%, #fda085 100%);
                min-height: 100vh;
            }
            .materie-row {
                margin-top: 60px;
            }
            .materie-card {
                background: #fff;
                border-radius: 16px;
                box-shadow: 0 2px 12px rgba(0,0,0,0.10);
                padding: 2rem 1rem;
                text-align: center;
                transition: transform 0.2s;
            }
            .materie-card:hover {
                transform: scale(1.05);
                box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            }
            .materie-link {
                font-size: 1.3rem;
                font-weight: bold;
                color: #2563eb;
                text-decoration: none;
                transition: color 0.2s;
            }
            .materie-link:hover {
                color: #1e40af;
                text-decoration: underline;
            }
            h1 {
                margin-top: 40px;
                font-size: 2.5rem;
                font-weight: bold;
                color: #2d3a4b;
                text-align: center;
            }
        </style>
    </head>

    <body>
       
<?php
session_start();
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>
        <main>
            <h1>Benvenuto nei Progetti Scolastici 25/26</h1>
            <div class="container materie-row">
                <div class="row justify-content-center">
                    <div class="col-md-3 mb-4">
                        <div class="materie-card">
                                <a class="materie-link" href="Informatica/INFORMATICA.php">INFORMATICA</a>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="materie-card">
                                <a class="materie-link" href="TEP/TEP.php">TEP</a>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="materie-card">
                                <a class="materie-link" href="GPO/GPO.php">GPO</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <footer>
            <!-- place footer here -->
        </footer>
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
