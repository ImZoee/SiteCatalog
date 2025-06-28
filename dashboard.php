<?php
session_start();
require_once "connect/config.php";
include DIR_BASE . "connect/connect.php";
include DIR_BASE . "admin-functions.php";

// Verificare dacă utilizatorul este autentificat
if (!isLogged()) {
    header("Location: login.php");
    exit();
}

// Obținere istoric descărcări
$downloads = getUserDownloadHistory($_SESSION['user_id']);

// Obținere invitații generate (dacă utilizatorul este admin)
$invitations = array();
if (isAdmin()) {
    $invitations = getInvitationsByUser($_SESSION['user_id']);
}

// Procesare formular pentru generare invitație (dacă utilizatorul este admin)
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_invitation']) && isAdmin()) {
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
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panou de Control - Catalog Electronic</title>
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
        .dashboard-header {
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
        .stats-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .stats-icon {
            font-size: 40px;
            margin-bottom: 15px;
            color: #3b5998;
        }
        .stats-title {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 5px;
        }
        .stats-value {
            font-size: 24px;
            font-weight: 700;
            color: #333;
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
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i> Panou de control
                        </a>
                    </li>
                    <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin.php">
                                <i class="fas fa-cog me-1"></i> Administrare
                            </a>
                        </li>
                    <?php endif; ?>
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

    <!-- Dashboard Header -->
    <header class="dashboard-header">
        <div class="container">
            <h1><i class="fas fa-tachometer-alt me-2"></i> Panou de Control</h1>
            <p class="lead mb-0">Bine ai venit, <?php echo htmlspecialchars($_SESSION['user_fullname']); ?>!</p>
        </div>
    </header>

    <!-- Dashboard Content -->
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
        
        <!-- Info banner for regular users -->
        <?php if (!isAdmin()): ?>
            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle me-2"></i> Bine ai venit în panoul tău de control! Aici poți vedea istoricul descărcărilor tale. Doar administratorii pot încărca noi aplicații APK.
                <?php if (count($downloads) === 0): ?>
                <hr>
                <p class="mb-0">Nu ai descărcat încă nicio aplicație. <a href="index.php" class="alert-link">Vizitează pagina principală</a> pentru a vedea aplicațiile disponibile.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Info banner for regular users -->
        <?php if (!isAdmin()): ?>
            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle me-2"></i> Bine ai venit în panoul tău de control! Aici poți vedea istoricul descărcărilor tale. Doar administratorii pot încărca noi aplicații APK.
                <?php if (count($downloads) === 0): ?>
                <hr>
                <p class="mb-0">Nu ai descărcat încă nicio aplicație. <a href="index.php" class="alert-link">Vizitează pagina principală</a> pentru a vedea aplicațiile disponibile.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-download"></i>
                    </div>
                    <div class="stats-title">TOTAL DESCĂRCĂRI</div>
                    <div class="stats-value"><?php echo count($downloads); ?></div>
                </div>
            </div>
            
            <?php if (isAdmin()): ?>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stats-title">INVITAȚII GENERATE</div>
                    <div class="stats-value"><?php echo count($invitations); ?></div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stats-title">INVITAȚII UTILIZATE</div>
                    <div class="stats-value">
                        <?php 
                        $used = 0;
                        foreach ($invitations as $inv) {
                            if ($inv['status'] == 'used') $used++;
                        }
                        echo $used;
                        ?>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="col-md-8">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stats-title">CONT CREAT LA</div>
                    <div class="stats-value"><?php echo date('d.m.Y', strtotime($_SESSION['user_created_at'] ?? date('Y-m-d'))); ?></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Recent Downloads -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-history me-2"></i> Istoricul descărcărilor
            </div>
            <div class="card-body">
                <?php if (empty($downloads)): ?>
                    <div class="alert alert-info">
                        Nu aveți nicio descărcare înregistrată.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Aplicație</th>
                                    <th>Versiune</th>
                                    <th>Data descărcării</th>
                                    <th>IP</th>
                                    <th>Acțiuni</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($downloads as $download): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($download['apk_name']); ?></td>
                                        <td><?php echo htmlspecialchars($download['apk_version']); ?></td>
                                        <td><?php echo date('d.m.Y H:i', strtotime($download['downloaded_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($download['ip_address']); ?></td>
                                        <td>
                                            <a href="index.php?comanda=download&id=<?php echo $download['apk_id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-download me-1"></i> Descarcă din nou
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (isAdmin()): ?>
        <!-- Invitation Generator (For Admin Users) -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-envelope me-2"></i> Generator invitații rapide
            </div>
            <div class="card-body">
                <form method="POST" action="dashboard.php" class="row">
                    <div class="col-md-8 mb-3 mb-md-0">
                        <input type="email" class="form-control" id="email" name="email" placeholder="Adresă email destinatar" required>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" name="generate_invitation" class="btn btn-primary w-100">
                            <i class="fas fa-paper-plane me-2"></i> Generează invitație
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Recent Invitations -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-list me-2"></i> Invitații recente
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
                                <?php 
                                // Afișăm doar ultimele 5 invitații
                                $recentInvitations = array_slice($invitations, 0, 5);
                                foreach ($recentInvitations as $invitation): 
                                ?>
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
                    
                    <?php if (count($invitations) > 5): ?>
                        <div class="text-center mt-3">
                            <a href="admin.php" class="btn btn-outline-primary">
                                <i class="fas fa-list me-2"></i> Vezi toate invitațiile
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
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
                    <?php if (isAdmin()): ?>
                        <a href="admin.php" class="text-white me-3">Administrare</a>
                    <?php endif; ?>
                    <a href="contact.php" class="text-white">Contact</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
