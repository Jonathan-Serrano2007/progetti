<?php 
session_start();
include_once 'database.php';
require_once 'tenant_context.php';

$tenant_id = musicare_get_request_tenant_id() ?? musicare_get_current_tenant_id();
$tenant_query = '?tenant=' . urlencode($tenant_id);
$login_url = 'login.php' . $tenant_query;
$register_url = 'register.php' . $tenant_query;

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php' . $tenant_query);
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Musicare - La piattaforma musicale per tutti</title>
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
            padding: 2rem 0;
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

        /* Plans Section */
        .plans {
            padding: 6rem 0;
            background: #0f0f0f;
        }

        .plan-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(29, 185, 84, 0.3);
            border-radius: 12px;
            padding: 2.5rem 2rem;
            text-align: center;
            transition: all 0.3s;
            position: relative;
            height: 100%;
        }

        .plan-card.featured {
            border-color: #1db954;
            background: rgba(29, 185, 84, 0.08);
            transform: scale(1.05);
        }

        .plan-card:hover {
            border-color: #1db954;
            box-shadow: 0 0 30px rgba(29, 185, 84, 0.2);
        }

        .plan-badge {
            display: inline-block;
            background: #1db954;
            color: #000;
            padding: 0.5rem 1rem;
            border-radius: 500px;
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .plan-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .plan-features {
            text-align: left;
            margin: 2rem 0;
            min-height: 200px;
        }

        .plan-features li {
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: #b3b3b3;
            font-size: 0.95rem;
        }

        .plan-features li:last-child {
            border-bottom: none;
        }

        .plan-cta {
            margin-top: 2rem;
        }

        /* Footer */
        footer {
            background: rgba(15, 15, 15, 0.95);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 3rem 0 1.5rem;
            text-align: center;
            color: #b3b3b3;
        }

        footer .footer-links {
            margin-bottom: 2rem;
        }

        footer a {
            color: #1db954;
            text-decoration: none;
            margin: 0 1rem;
            transition: 0.3s;
        }

        footer a:hover {
            color: #1ed760;
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

        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }
            
            .hero-content p {
                font-size: 1rem;
            }

            .plan-card.featured {
                transform: scale(1);
            }
        }
    </style>
    <link href="musicare-theme.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid px-4">
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
                    <li class="nav-item">
                        <a class="nav-link" href="#plans">Piani</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn-primary-custom ms-2" href="<?php echo htmlspecialchars($login_url, ENT_QUOTES, 'UTF-8'); ?>">Accedi</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero">
        <div class="hero-content">
            <h1>🎵 La tua musica, il tuo mondo</h1>
            <p>Scopri una comunità di musicisti appassionati. Condividi, impara e crea insieme</p>
            <div class="cta-buttons">
                <a href="<?php echo htmlspecialchars($login_url, ENT_QUOTES, 'UTF-8'); ?>" class="btn-primary-custom">Accedi</a>
                <a href="<?php echo htmlspecialchars($register_url, ENT_QUOTES, 'UTF-8'); ?>" class="btn-secondary-custom">Registrati</a>
            </div>
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
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-music-note-beamed"></i>
                        </div>
                        <h3>Esercizi Musicali</h3>
                        <p>Completa esercizi di base e avanzati per migliorare le tue abilità musicali</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <h3>Statistiche Dettagliate</h3>
                        <p>Traccia i tuoi progressi con grafici e analitiche personalizzate</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <h3>Comunità Globale</h3>
                        <p>Connettiti con musicisti da tutto il mondo e scopri nuovi talenti</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h3>Accesso Sicuro</h3>
                        <p>Autentica con JWT e gestisci i tuoi dati in sicurezza</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-sliders"></i>
                        </div>
                        <h3>Ruoli Personalizzati</h3>
                        <p>Tre livelli di accesso: Registrato, Pro e Admin</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-gear"></i>
                        </div>
                        <h3>Gestione Avanzata</h3>
                        <p>Dashboard admin per gestire utenti ed esercizi</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Plans Section -->
    <section id="plans" class="plans">
        <div class="container">
            <div class="text-center mb-5">
                <h2 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem;">Scegli il tuo piano</h2>
                <p style="color: #b3b3b3; font-size: 1.1rem;">Accedi a tutte le feature di cui hai bisogno</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="plan-card">
                        <div class="plan-badge">GRATUITO</div>
                        <div class="plan-name">Registrato</div>
                        <p style="color: #b3b3b3; margin-bottom: 0;">Perfetto per iniziare</p>
                        <ul class="plan-features list-unstyled">
                            <li><i class="bi bi-check-circle"></i> Esercizi di Base</li>
                            <li><i class="bi bi-check-circle"></i> 5 Esercizi Disponibili</li>
                            <li><i class="bi bi-check-circle"></i> Statistiche Personali</li>
                            <li><i class="bi bi-check-circle"></i> Accesso Comunità</li>
                            <li><i class="bi bi-x-circle"></i> Esercizi Avanzati</li>
                        </ul>
                        <div class="plan-cta">
                            <a href="<?php echo htmlspecialchars($register_url, ENT_QUOTES, 'UTF-8'); ?>" class="btn-secondary-custom">Registrati Gratis</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="plan-card featured">
                        <div class="plan-badge">CONSIGLIATO</div>
                        <div class="plan-name">Pro</div>
                        <p style="color: #b3b3b3; margin-bottom: 0;">Per musicisti seri</p>
                        <ul class="plan-features list-unstyled">
                            <li><i class="bi bi-check-circle"></i> Tutti i vantaggi Registrato</li>
                            <li><i class="bi bi-check-circle"></i> Esercizi Avanzati</li>
                            <li><i class="bi bi-check-circle"></i> Statistiche Avanzate</li>
                            <li><i class="bi bi-check-circle"></i> Grafici Dettagliati</li>
                            <li><i class="bi bi-check-circle"></i> Priorità nel Support</li>
                        </ul>
                        <div class="plan-cta">
                            <a href="<?php echo htmlspecialchars($register_url, ENT_QUOTES, 'UTF-8'); ?>" class="btn-primary-custom">Iscriviti Pro</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="plan-card">
                        <div class="plan-badge">ESCLUSIVO</div>
                        <div class="plan-name">Admin</div>
                        <p style="color: #b3b3b3; margin-bottom: 0;">Gestisci la piattaforma</p>
                        <ul class="plan-features list-unstyled">
                            <li><i class="bi bi-check-circle"></i> Tutti i vantaggi Pro</li>
                            <li><i class="bi bi-check-circle"></i> Gestione Utenti</li>
                            <li><i class="bi bi-check-circle"></i> Gestione Esercizi</li>
                            <li><i class="bi bi-check-circle"></i> Statistiche Platform</li>
                            <li><i class="bi bi-check-circle"></i> Accesso Completo API</li>
                        </ul>
                        <div class="plan-cta">
                            <a href="API/test.html" class="btn-secondary-custom">Accedi API</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-links">
                <a href="<?php echo htmlspecialchars($login_url, ENT_QUOTES, 'UTF-8'); ?>"><i class="bi bi-box-arrow-in-right"></i> Accedi</a>
                <a href="<?php echo htmlspecialchars($register_url, ENT_QUOTES, 'UTF-8'); ?>"><i class="bi bi-person-plus"></i> Registrati</a>
                <a href="API/test.html"><i class="bi bi-gear"></i> API Test</a>
            </div>
            <p>&copy; 2024 Musicare - La piattaforma musicale per tutti</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
