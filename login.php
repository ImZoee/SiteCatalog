<?php
session_start();
require_once "connect/config.php";
include DIR_BASE . "connect/connect.php";
include DIR_BASE . "admin-functions.php";

// Redirectare dacă utilizatorul este deja autentificat
if (isLogged()) {
    header("Location: index.php");
    exit();
}

$error = '';

// Procesare formular de autentificare
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($username) || empty($password)) {
        $error = "Completați toate câmpurile.";
    } else {
        if (!doLogin($username, $password)) {
            $error = "Autentificare eșuată! Verificați numele de utilizator și parola.";
        } else {
            header("Location: index.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autentificare - Catalog Electronic</title>
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
        .login-container {
            max-width: 400px;
            margin: 80px auto;
        }
        .login-card {
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .login-header {
            background-color: #3b5998;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .login-body {
            padding: 30px;
        }
        .footer {
            background-color: #3b5998;
            color: white;
            padding: 20px 0;
            margin-top: 60px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-tablet-alt me-2"></i>
                Catalog Electronic
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home me-1"></i> Acasă
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="login.php">
                            <i class="fas fa-sign-in-alt me-1"></i> Autentificare
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">
                            <i class="fas fa-user-plus me-1"></i> Înregistrare
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Login Form -->
    <div class="container login-container">
        <div class="card login-card">
            <div class="login-header">
                <h3><i class="fas fa-sign-in-alt me-2"></i> Autentificare</h3>
                <p class="mb-0">Accesează contul tău pentru a descărca aplicația</p>
            </div>
            <div class="login-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="login.php">
                    <div class="mb-3">
                        <label for="username" class="form-label">Nume utilizator</label>
                        <input type="text" class="form-control" id="username" name="username" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Parolă</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i> Autentificare
                        </button>
                    </div>
                </form>
                
                <div class="mt-3 text-center">
                    <p>Nu ai cont? <a href="register.php">Înregistrează-te</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p>&copy; <?php echo date('Y'); ?> Catalog Electronic. Toate drepturile rezervate.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="index.php" class="text-white me-3">Acasă</a>
                    <a href="register.php" class="text-white me-3">Înregistrare</a>
                    <a href="contact.php" class="text-white">Contact</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>