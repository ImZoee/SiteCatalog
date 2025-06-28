<?php
session_start();
require_once "connect/config.php";
include DIR_BASE . "connect/connect.php";
include DIR_BASE . "admin-functions.php";

// Verifică doar dacă directoarele există
$upload_dir = dirname(__FILE__) . '/uploads/apk/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Gestionare comenzi
$comanda = isset($_REQUEST["comanda"]) ? $_REQUEST["comanda"] : NULL;

// Comenzi existente pentru autentificare și altele
if (isset($comanda)) {
    switch ($comanda) {
        case 'login':
            $nume = isset($_REQUEST["username"]) ? $_REQUEST["username"] : '';
            $parola = isset($_REQUEST["password"]) ? $_REQUEST["password"] : '';
            if (!doLogin($nume, $parola)) {
                echo "<div class='alert alert-danger'>Autentificare esuata!</div>";
            }
            break;

        case 'logout':
            doLogout();
            header("Location: index.php");
            exit();
            
        case 'download':
            if (!isLogged()) {
                header("Location: login.php");
                exit();
            }
            
            $apkId = isset($_REQUEST["id"]) ? intval($_REQUEST["id"]) : 0;
            $result = downloadApk($apkId, $_SESSION['user_id']);
            
            if ($result['success']) {
                $filePath = dirname(__FILE__) . '/' . $result['file_path'];
                
                if (file_exists($filePath)) {
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/vnd.android.package-archive');
                    header('Content-Disposition: attachment; filename="' . $result['file_name'] . '"');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($filePath));
                    readfile($filePath);
                    exit;
                } else {
                    $error = "Fișierul nu a fost găsit pe server.";
                }
            } else {
                $error = $result['message'];
            }
            break;
    }
}

// Verificăm dacă utilizatorul este autentificat
$is_logged = isLogged();
$username = getLoggedUser();

// Obținem lista de APK-uri disponibile dacă utilizatorul este autentificat
$apkFiles = array();
if ($is_logged) {
    $apkFiles = getApkFiles('active');
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalog Electronic - Descărcare Aplicație</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #3b5998;
        }
        .navbar-brand, .nav-link {
            color: white !important;
        }
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1563986768494-4dee2763ff3f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        .app-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .app-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .app-icon {
            width: 80px;
            height: 80px;
            object-fit: cover;
            margin-right: 15px;
        }
        .footer {
            background-color: #3b5998;
            color: white;
            padding: 40px 0 20px;
            margin-top: 60px;
        }
        .features-section {
            padding: 60px 0;
        }
        .feature-box {
            padding: 30px;
            text-align: center;
            border-radius: 10px;
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
            height: 100%;
        }
        .feature-box:hover {
            transform: translateY(-10px);
        }
        .feature-icon {
            font-size: 40px;
            margin-bottom: 20px;
            color: #3b5998;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-tablet-alt me-2"></i>
                Catalog Electronic Debugging
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if ($is_logged): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-1"></i> Panou de control
                            </a>
                        </li>
                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin.php">
                                    <i class="fas fa-cog me-1"></i> Administrare
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($username); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="profile.php">Profil</a></li>
                                <li><a class="dropdown-item" href="downloads.php">Descărcările mele</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="index.php?comanda=logout">Deconectare</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt me-1"></i> Autentificare
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">
                                <i class="fas fa-user-plus me-1"></i> Înregistrare
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <?php if (!$is_logged): ?>
    <!-- Hero Section for Non-Logged Users -->
    <section class="hero-section">
        <div class="container">
            <h1 class="display-4 mb-4">Catalog Electronic</h1>
            <p class="lead mb-4">Descarcă aplicația noastră pentru acces complet la catalogul electronic. Accesul este disponibil doar pe bază de invitație.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="login.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-sign-in-alt me-2"></i> Autentificare
                </a>
                <a href="register.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-user-plus me-2"></i> Înregistrare
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <h2 class="text-center mb-5">Beneficiile aplicației noastre</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <h4>Acces Securizat</h4>
                        <p>Aplicația noastră oferă acces doar utilizatorilor autorizați, cu sistem de autentificare securizat.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <h4>Actualizări Regulate</h4>
                        <p>Beneficiați de actualizări periodice cu informații și funcționalități noi în aplicație.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h4>Design Responsive</h4>
                        <p>Interfață intuitivă și ușor de utilizat, optimizată pentru toate tipurile de dispozitive mobile.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Cum funcționează</h2>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary text-white rounded-circle p-3 me-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <h4 class="m-0">1</h4>
                                </div>
                                <h5 class="m-0">Primește o invitație</h5>
                            </div>
                            <p>Pentru a avea acces la aplicația noastră, trebuie să primești o invitație de la un utilizator existent sau administrator.</p>
                        </div>
                    </div>
                    
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary text-white rounded-circle p-3 me-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <h4 class="m-0">2</h4>
                                </div>
                                <h5 class="m-0">Creează un cont</h5>
                            </div>
                            <p>Folosește codul de invitație primit pentru a-ți crea un cont pe platforma noastră.</p>
                        </div>
                    </div>
                    
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary text-white rounded-circle p-3 me-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <h4 class="m-0">3</h4>
                                </div>
                                <h5 class="m-0">Descarcă aplicația</h5>
                            </div>
                            <p>După autentificare, vei avea acces la toate versiunile disponibile ale aplicației noastre pentru descărcare.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php else: ?>
    <!-- APK Download Section for Logged Users -->
    <section class="py-5">
        <div class="container">
            <h2 class="mb-4">Aplicații disponibile pentru descărcare</h2>
            
            <?php if (!isAdmin()): ?>
                <div class="alert alert-info mb-4">
                    <i class="fas fa-info-circle me-2"></i> Poți descărca oricare dintre aplicațiile disponibile mai jos. Doar administratorii pot încărca noi aplicații în sistem.
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($apkFiles)): ?>
                <div class="alert alert-info">
                    Nu există aplicații disponibile pentru descărcare momentan.
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($apkFiles as $apk): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card app-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div>
                                            <h5 class="card-title mb-1"><?php echo htmlspecialchars($apk['name']); ?></h5>
                                            <p class="text-muted mb-0">Versiunea <?php echo htmlspecialchars($apk['version']); ?></p>
                                        </div>
                                    </div>
                                    
                                    <p class="card-text"><?php echo nl2br(htmlspecialchars($apk['description'])); ?></p>
                                    
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div>
                                            <small class="text-muted">
                                                <i class="fas fa-download me-1"></i> <?php echo number_format($apk['downloads']); ?> descărcări
                                            </small>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-file me-1"></i> <?php echo round($apk['file_size'] / (1024 * 1024), 2); ?> MB
                                            </small>
                                        </div>
                                        <a href="index.php?comanda=download&id=<?php echo $apk['id']; ?>" class="btn btn-primary">
                                            <i class="fas fa-download me-2"></i> Descarcă
                                        </a>
                                    </div>
                                </div>
                                <div class="card-footer bg-light">
                                    <small class="text-muted">
                                        Adăugat la <?php echo date('d.m.Y', strtotime($apk['created_at'])); ?> de <?php echo htmlspecialchars($apk['uploaded_by_username']); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5>Catalog Electronic</h5>
                    <p>Platforma noastră oferă acces securizat la aplicația de catalog electronic, disponibilă exclusiv pentru utilizatorii invitați.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Link-uri rapide</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white">Acasă</a></li>
                        <?php if ($is_logged): ?>
                            <li><a href="dashboard.php" class="text-white">Panou de control</a></li>
                            <li><a href="downloads.php" class="text-white">Descărcările mele</a></li>
                        <?php else: ?>
                            <li><a href="login.php" class="text-white">Autentificare</a></li>
                            <li><a href="register.php" class="text-white">Înregistrare</a></li>
                        <?php endif; ?>
                        <li><a href="contact.php" class="text-white">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Contact</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-envelope me-2"></i> pavelmarius28@yahoo.com</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4 bg-light">
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> Catalog Electronic. Toate drepturile rezervate.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
