<?php
session_start();
require_once "connect/config.php";
include DIR_BASE . "connect/connect.php";
include DIR_BASE . "admin-functions.php";

// Verificare dacă utilizatorul este admin
if (!isLogged() || !isAdmin()) {
    header("Location: index.php");
    exit();
}

$message = '';
$error = '';

// Procesare formular pentru generare invitație
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_invitation'])) {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Introduceți o adresă de email validă.";
    } else {
        $result = createInvitation($email, $_SESSION['user_id']);
        
        if ($result['success']) {
            $message = "Cod de invitație generat cu succes: " . $result['code'];
        } else {
            $error = $result['message'];
        }
    }
}

// Procesare formular pentru încărcare APK
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['upload_apk']) || isset($_POST['form_submit']) && $_POST['form_submit'] === 'upload_apk')) {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $version = isset($_POST['version']) ? trim($_POST['version']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    
    error_log("Procesare formular upload APK: " . print_r($_POST, true));
    error_log("Fișiere încărcate: " . print_r($_FILES, true));
    
    if (empty($name) || empty($version)) {
        $error = "Numele și versiunea sunt obligatorii.";
    } elseif (!isset($_FILES['apk_file']) || $_FILES['apk_file']['error'] != 0) {
        $error = "Selectați un fișier APK valid. Cod eroare: " . (isset($_FILES['apk_file']) ? $_FILES['apk_file']['error'] : 'Niciun fișier încărcat');
    } else {
        $result = uploadApk($name, $version, $description, $_FILES['apk_file'], $_SESSION['user_id']);
        
        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

// Obținere liste de invitații și APK-uri
$invitations = getInvitationsByUser($_SESSION['user_id']);
$apkFiles = getApkFiles();
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrare - Catalog Electronic</title>
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
        .admin-header {
            background-color: #3b5998;
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        .card {
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            font-weight: 600;
        }
        .footer {
            background-color: #3b5998;
            color: white;
            padding: 20px 0;
            margin-top: 60px;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .nav-pills .nav-link.active {
            background-color: #3b5998;
        }
        .nav-pills .nav-link {
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
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i> Panou de control
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="admin.php">
                            <i class="fas fa-cog me-1"></i> Administrare
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($_SESSION['user']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php">Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="index.php?comanda=logout">Deconectare</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Admin Header -->
    <header class="admin-header">
        <div class="container">
            <h1><i class="fas fa-cog me-2"></i> Panou Administrare</h1>
            <p class="lead mb-0">Gestionează invitațiile și aplicațiile APK disponibile</p>
        </div>
    </header>

    <!-- Admin Content -->
    <div class="container">
        <?php if (!empty($message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i> <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <!-- Admin Tabs -->
        <ul class="nav nav-pills mb-4" id="adminTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="invitations-tab" data-bs-toggle="pill" data-bs-target="#invitations" type="button" role="tab">
                    <i class="fas fa-envelope me-2"></i> Invitații
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="apk-tab" data-bs-toggle="pill" data-bs-target="#apk" type="button" role="tab">
                    <i class="fas fa-file-alt me-2"></i> Aplicații APK
                </button>
            </li>
        </ul>
        
        <div class="tab-content" id="adminTabContent">
            <!-- Invitations Tab -->
            <div class="tab-pane fade show active" id="invitations" role="tabpanel" aria-labelledby="invitations-tab">
                <div class="row">
                    <div class="col-md-5">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-plus-circle me-2"></i> Generare cod invitație nou
                            </div>
                            <div class="card-body">
                                <form method="POST" action="admin.php">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Adresă email destinatar</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                        <small class="form-text text-muted">Codul de invitație va fi asociat cu această adresă de email.</small>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" name="generate_invitation" class="btn btn-primary">
                                            <i class="fas fa-paper-plane me-2"></i> Generează invitație
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-7">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-list me-2"></i> Coduri de invitație generate
                            </div>
                            <div class="card-body">
                                <?php if (empty($invitations)): ?>
                                    <div class="alert alert-info">
                                        Nu ați generat încă niciun cod de invitație.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Cod</th>
                                                    <th>Email</th>
                                                    <th>Status</th>
                                                    <th>Data generării</th>
                                                    <th>Utilizat de</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($invitations as $invitation): ?>
                                                    <tr>
                                                        <td><code><?php echo htmlspecialchars($invitation['code']); ?></code></td>
                                                        <td><?php echo htmlspecialchars($invitation['email']); ?></td>
                                                        <td>
                                                            <?php if ($invitation['status'] == 'active'): ?>
                                                                <span class="badge bg-success">Activ</span>
                                                            <?php elseif ($invitation['status'] == 'used'): ?>
                                                                <span class="badge bg-primary">Utilizat</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-danger">Expirat</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?php echo date('d.m.Y H:i', strtotime($invitation['created_at'])); ?></td>
                                                        <td>
                                                            <?php if ($invitation['status'] == 'used'): ?>
                                                                <?php echo htmlspecialchars($invitation['used_by_username']); ?>
                                                            <?php else: ?>
                                                                -
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- APK Files Tab -->
            <div class="tab-pane fade" id="apk" role="tabpanel" aria-labelledby="apk-tab">
                <div class="row">
                    <div class="col-md-5">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-upload me-2"></i> Încărcare aplicație APK nouă (Doar pentru administratori)
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info mb-3">
                                    <i class="fas fa-info-circle me-2"></i> Încărcarea aplicațiilor APK este permisă doar administratorilor. Utilizatorii obișnuiți pot doar descărca aplicațiile disponibile.
                                </div>
                                <form method="POST" action="admin.php" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nume aplicație</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="version" class="form-label">Versiune</label>
                                        <input type="text" class="form-control" id="version" name="version" placeholder="ex: 1.0.0" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Descriere</label>
                                        <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="apk_file" class="form-label">Fișier APK</label>
                                        <input type="file" class="form-control" id="apk_file" name="apk_file" accept=".apk" required>
                                        <small class="form-text text-muted">Selectați un fișier APK valid (max. 100MB).</small>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" name="upload_apk" class="btn btn-primary">
                                            <i class="fas fa-cloud-upload-alt me-2"></i> Încarcă aplicație
                                        </button>
                                    </div>
                                    <!-- Câmp ascuns pentru a se asigura că formularul este trimis corect -->
                                    <input type="hidden" name="form_submit" value="upload_apk">
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-7">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-list me-2"></i> Aplicații APK disponibile
                            </div>
                            <div class="card-body">
                                <?php if (empty($apkFiles)): ?>
                                    <div class="alert alert-info">
                                        Nu există aplicații APK încărcate.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Nume</th>
                                                    <th>Versiune</th>
                                                    <th>Mărime</th>
                                                    <th>Descărcări</th>
                                                    <th>Data</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($apkFiles as $apk): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($apk['name']); ?></td>
                                                        <td><?php echo htmlspecialchars($apk['version']); ?></td>
                                                        <td><?php echo round($apk['file_size'] / (1024 * 1024), 2); ?> MB</td>
                                                        <td><?php echo number_format($apk['downloads']); ?></td>
                                                        <td><?php echo date('d.m.Y', strtotime($apk['created_at'])); ?></td>
                                                        <td>
                                                            <?php if ($apk['status'] == 'active'): ?>
                                                                <span class="badge bg-success">Activ</span>
                                                            <?php elseif ($apk['status'] == 'inactive'): ?>
                                                                <span class="badge bg-danger">Inactiv</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-warning text-dark">Deprecated</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
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
                    <a href="dashboard.php" class="text-white me-3">Panou de control</a>
                    <a href="contact.php" class="text-white">Contact</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
