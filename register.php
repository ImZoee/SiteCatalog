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
$success = '';
$invitation_valid = false;
$invitation_data = null;

// Verificarea codului de invitație
if (isset($_GET['code']) && !empty($_GET['code'])) {
    $invitation_data = validateInvitationCode($_GET['code']);
    if ($invitation_data) {
        $invitation_valid = true;
    } else {
        $error = "Codul de invitație este invalid sau a fost deja utilizat.";
    }
}

// Procesarea formularului de înregistrare
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
    $invitation_code = isset($_POST['invitation_code']) ? trim($_POST['invitation_code']) : '';
    
    // Validare câmpuri
    if (empty($username) || empty($password) || empty($confirm_password) || empty($email) || empty($fullname) || empty($invitation_code)) {
        $error = "Toate câmpurile sunt obligatorii.";
    } elseif ($password !== $confirm_password) {
        $error = "Parolele nu coincid.";
    } elseif (strlen($password) < 6) {
        $error = "Parola trebuie să aibă minim 6 caractere.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresa de email nu este validă.";
    } else {
        // Verifică codul de invitație și înregistrează utilizatorul
        $result = registerUser($username, $password, $email, $fullname, $invitation_code);
        
        if ($result['success']) {
            $success = $result['message'];
            // Redirecționare către pagina de login după 2 secunde
            header("refresh:2;url=login.php");
        } else {
            $error = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Înregistrare - Catalog Electronic</title>
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
        .register-container {
            max-width: 500px;
            margin: 50px auto;
        }
        .register-card {
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .register-header {
            background-color: #3b5998;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .register-body {
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
                        <a class="nav-link" href="login.php">
                            <i class="fas fa-sign-in-alt me-1"></i> Autentificare
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="register.php">
                            <i class="fas fa-user-plus me-1"></i> Înregistrare
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Register Form -->
    <div class="container register-container">
        <div class="card register-card">
            <div class="register-header">
                <h3><i class="fas fa-user-plus me-2"></i> Înregistrare cont nou</h3>
                <p class="mb-0">Creează un cont pentru a accesa catalogul electronic</p>
            </div>
            <div class="register-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!$invitation_valid && empty($success)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Pentru a te înregistra, ai nevoie de un cod de invitație valid.
                    </div>
                    
                    <form method="GET" action="register.php">
                        <div class="mb-3">
                            <label for="code" class="form-label">Cod invitație</label>
                            <input type="text" class="form-control" id="code" name="code" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Verifică codul</button>
                        </div>
                    </form>
                <?php elseif (empty($success)): ?>
                    <form method="POST" action="register.php">
                        <div class="mb-3">
                            <label for="username" class="form-label">Nume utilizator</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $invitation_data ? htmlspecialchars($invitation_data['email']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="fullname" class="form-label">Nume complet</label>
                            <input type="text" class="form-control" id="fullname" name="fullname" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Parolă</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <small class="form-text text-muted">Parola trebuie să aibă minim 6 caractere.</small>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirmare parolă</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="invitation_code" class="form-label">Cod invitație</label>
                            <input type="text" class="form-control" id="invitation_code" name="invitation_code" value="<?php echo isset($_GET['code']) ? htmlspecialchars($_GET['code']) : ''; ?>" readonly required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="register" class="btn btn-primary">Înregistrare</button>
                        </div>
                    </form>
                <?php endif; ?>
                
                <div class="mt-3 text-center">
                    <p>Ai deja un cont? <a href="login.php">Autentifică-te</a></p>
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
                    <a href="login.php" class="text-white me-3">Autentificare</a>
                    <a href="contact.php" class="text-white">Contact</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
