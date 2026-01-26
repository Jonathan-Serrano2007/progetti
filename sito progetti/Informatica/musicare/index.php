<?php 
session_start();

// import database
include_once 'database.php';

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Musicare - La tua piattaforma musicale</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #0f0f0f;
            color: #fff;
            line-height: 1.6;
        }

        /* Navigation */
        nav {
            background: rgba(15, 15, 15, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #1db954 0%, #1ed760 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-link {
            color: #fff !important;
            font-weight: 500;
            position: relative;
            transition: 0.3s;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: #1db954;
            transition: width 0.3s;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f0f0f 0%, #1a1a1a 100%);
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(29, 185, 84, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
        }

        .hero-content {
            text-align: center;
            z-index: 2;
            max-width: 700px;
            animation: fadeInUp 0.8s ease-out;
        }

        .hero-content h1 {
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero-content p {
            font-size: 1.2rem;
            color: #b3b3b3;
            margin-bottom: 2rem;
            font-weight: 300;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary-custom {
            background: #1db954;
            color: #fff;
            padding: 0.75rem 2rem;
            border-radius: 500px;
            border: none;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary-custom:hover {
            background: #1ed760;
            transform: scale(1.05);
            color: #fff;
        }

        .btn-secondary-custom {
            background: transparent;
            color: #fff;
            padding: 0.75rem 2rem;
            border-radius: 500px;
            border: 2px solid #fff;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-secondary-custom:hover {
            background: #fff;
            color: #0f0f0f;
        }

        /* Features Section */
        .features {
            padding: 6rem 0;
            background: #1a1a1a;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 2rem;
            border-radius: 12px;
            text-align: center;
            transition: all 0.3s;
            height: 100%;
        }

        .feature-card:hover {
            background: rgba(29, 185, 84, 0.1);
            border-color: #1db954;
            transform: translateY(-10px);
        }

        .feature-icon {
            font-size: 3rem;
            color: #1db954;
            margin-bottom: 1rem;
        }

        .feature-card h3 {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .feature-card p {
            color: #b3b3b3;
            font-weight: 300;
        }

        /* Modal Styles */
        .modal-content {
            background: #1a1a1a;
            border: 1px solid rgba(29, 185, 84, 0.3);
        }

        .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-header .btn-close {
            filter: invert(1);
        }

        .nav-tabs {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .nav-tabs .nav-link {
            color: #b3b3b3;
            border-bottom: 2px solid transparent;
            transition: 0.3s;
        }

        .nav-tabs .nav-link.active {
            color: #1db954;
            border-bottom-color: #1db954;
            background: transparent;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            padding: 0.75rem 1rem;
            border-radius: 8px;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: #1db954;
            color: #fff;
            box-shadow: 0 0 0 0.2rem rgba(29, 185, 84, 0.25);
        }

        .form-control::placeholder {
            color: #666;
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            border-color: #dc3545;
            color: #ff7c7c;
        }

        .alert-success {
            background: rgba(29, 185, 84, 0.1);
            border-color: #1db954;
            color: #1ed760;
        }

        /* Logged in home */
        .logged-home {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .user-header {
            text-align: center;
            padding: 4rem 2rem;
        }

        .user-header h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #1db954 0%, #1ed760 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .user-header p {
            font-size: 1.1rem;
            color: #b3b3b3;
            margin-bottom: 2rem;
        }

        .user-menu {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .btn-logout {
            background: #dc3545;
            color: #fff;
            padding: 0.75rem 2rem;
            border-radius: 500px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-logout:hover {
            background: #ff5d5d;
        }

        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }
            
            .hero-content p {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-music-note-beamed"></i> Musicare
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Caratteristiche</a>
                    </li>
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item">
                            <span class="nav-link" style="cursor: default;">Ciao, <?php echo htmlspecialchars($_SESSION['user_nome']); ?></span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?logout=1">Esci</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <?php if ($isLoggedIn): ?>
        <!-- Logged In Home -->
        <div class="logged-home">
            <div class="user-header">
                <h1>🎵 Benvenuto, <?php echo htmlspecialchars($_SESSION['user_nome']); ?>!</h1>
                <p>Sei entrato nella comunità musicale di Musicare</p>
                
                <div class="user-menu">
                    <a href="index.php?logout=1" class="btn-logout">
                        <i class="bi bi-box-arrow-left"></i> Esci
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Hero Section -->
        <div class="hero">
            <div class="hero-content">
                <h1>La tua musica, il tuo mondo</h1>
                <p>Scopri una comunità di musicisti appassionati. Condividi, impara e crea insieme</p>
            </div>
        </div>

        <!-- Features Section -->
        <section id="features" class="features">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem;">Cosa offriamo</h2>
                    <p style="color: #b3b3b3; font-size: 1.1rem;">Tutto ciò di cui hai bisogno per la tua passione musicale</p>
                </div>
                <div class="row g-4">
                    <div class="col-md-6 col-lg-3">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="bi bi-music-note"></i>
                            </div>
                            <h3>Condivisione Musicale</h3>
                            <p>Condividi i tuoi brani e scopri artisti emergenti dalla comunità</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="bi bi-people"></i>
                            </div>
                            <h3>Comunità</h3>
                            <p>Connettiti con altri musicisti e crea collaborazioni straordinarie</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="bi bi-pencil"></i>
                            </div>
                            <h3>Creazione</h3>
                            <p>Sviluppa le tue competenze con risorse e tutorial dedicati</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="bi bi-star"></i>
                            </div>
                            <h3>Opportunità</h3>
                            <p>Scopri nuove opportunità nel mondo della musica digitale</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
