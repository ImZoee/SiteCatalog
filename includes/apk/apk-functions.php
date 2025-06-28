<?php
function uploadApk($name, $version, $description, $file, $uploadedBy) {
    global $id_conexiune;
    
    // Log pentru debugging
    error_log("Încercare de upload APK: " . print_r($file, true));
    
    // Verifică dacă directorul de upload există
    $uploadDir = dirname(dirname(dirname(__FILE__))) . '/uploads/apk/';
    
    // Asigură-te că directorul există și are permisiunile corecte
    if (!file_exists(dirname($uploadDir))) {
        if (!mkdir(dirname($uploadDir), 0777, true)) {
            error_log("Nu s-a putut crea directorul: " . dirname($uploadDir));
            return array('success' => false, 'message' => 'Nu s-a putut crea directorul de upload principal.');
        }
        chmod(dirname($uploadDir), 0777);
        error_log("Director principal creat cu succes: " . dirname($uploadDir));
    }
    
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            error_log("Nu s-a putut crea directorul: " . $uploadDir);
            return array('success' => false, 'message' => 'Nu s-a putut crea directorul de upload pentru APK-uri.');
        }
        chmod($uploadDir, 0777);
        error_log("Director APK creat cu succes: " . $uploadDir);
    }
    
    // Asigură-te că directorul are permisiunile corecte
    chmod(dirname($uploadDir), 0777);
    chmod($uploadDir, 0777);
    
    // Verifică dacă fișierul este un APK valid
    $fileType = pathinfo($file['name'], PATHINFO_EXTENSION);
    error_log("Tip fișier: " . $fileType);
    if ($fileType != 'apk') {
        return array('success' => false, 'message' => 'Doar fișierele APK sunt permise.');
    }
    
    // Generează un nume unic pentru fișier
    $fileName = uniqid() . '_' . str_replace(' ', '_', $name) . '_v' . $version . '.apk';
    $targetFile = $uploadDir . $fileName;
    
    error_log("Încercare de a muta fișierul la: " . $targetFile);
    
    // Verifică permisiunile fișierului temporar
    error_log("Permisiuni fișier temporar: " . substr(sprintf('%o', fileperms($file['tmp_name'])), -4));
    
    // Verifică permisiunile directorului destinație
    error_log("Permisiuni director destinație: " . substr(sprintf('%o', fileperms($uploadDir)), -4));
    
    // Verifică dacă putem scrie în directorul destinație
    if (!is_writable($uploadDir)) {
        error_log("Directorul nu este inscriptibil: " . $uploadDir);
        // Încearcă să seteze permisiunile
        chmod($uploadDir, 0777);
        if (!is_writable($uploadDir)) {
            return array('success' => false, 'message' => 'Directorul de upload nu are permisiuni de scriere. Rulați fix_permissions.sh ca administrator.');
        }
    }
    
    // Încearcă să mute fișierul
    $upload_success = false;
    
    // Prima metodă: move_uploaded_file
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        $upload_success = true;
        error_log("Fișier mutat cu succes folosind move_uploaded_file la: " . $targetFile);
    } else {
        error_log("Eșec la move_uploaded_file. Încerc metoda alternativă cu copy...");
        
        // A doua metodă: copy
        if (copy($file['tmp_name'], $targetFile)) {
            $upload_success = true;
            error_log("Fișier copiat cu succes folosind copy la: " . $targetFile);
            unlink($file['tmp_name']); // Șterge fișierul temporar
        } else {
            error_log("Ambele metode de upload au eșuat. Cod eroare: " . $file['error']);
        }
    }
    
    if ($upload_success) {
        // Setează permisiuni pentru fișierul încărcat
        chmod($targetFile, 0644);
        
        $name = mysqli_real_escape_string($id_conexiune, $name);
        $version = mysqli_real_escape_string($id_conexiune, $version);
        $description = mysqli_real_escape_string($id_conexiune, $description);
        $filePath = 'uploads/apk/' . $fileName;
        $fileSize = $file['size'];
        $uploadedBy = (int) $uploadedBy;
        
        $sql = "INSERT INTO apk_files (name, version, description, file_path, file_size, uploaded_by) 
                VALUES ('$name', '$version', '$description', '$filePath', $fileSize, $uploadedBy)";
        
        error_log("SQL Query: " . $sql);
        
        // Verificăm dacă tabela apk_files există
        $check_table = mysqli_query($id_conexiune, "SHOW TABLES LIKE 'apk_files'");
        if (mysqli_num_rows($check_table) == 0) {
            error_log("Tabela apk_files nu există în baza de date");
            return array('success' => false, 'message' => 'Tabela apk_files nu există în baza de date. Rulați scriptul database_setup.sql pentru a crea tabela.');
        }
        
        if (mysqli_query($id_conexiune, $sql)) {
            error_log("Înregistrare adăugată cu succes în baza de date");
            return array('success' => true, 'message' => 'Fișierul APK a fost încărcat cu succes!');
        } else {
            // Dacă inserarea în baza de date eșuează, șterge fișierul
            error_log("Eroare la inserarea în baza de date: " . mysqli_error($id_conexiune));
            @unlink($targetFile);
            return array('success' => false, 'message' => 'A apărut o eroare la salvarea informațiilor în baza de date: ' . mysqli_error($id_conexiune));
        }
    } else {
        error_log("Eroare la încărcarea fișierului. Cod eroare: " . $file['error'] . ". Mesaj: " . $php_errormsg);
        return array(
            'success' => false, 
            'message' => 'A apărut o eroare la încărcarea fișierului. Verificați permisiunile directorului uploads/apk. ' .
                      'Puteți rula scriptul fix_permissions.sh ca administrator pentru a rezolva problema. ' .
                      'Cod eroare: ' . $file['error']
        );
    }
}

function getApkFiles($status = 'active') {
    global $id_conexiune;
    
    $status = mysqli_real_escape_string($id_conexiune, $status);
    
    $sql = "SELECT a.*, u.username as uploaded_by_username 
            FROM apk_files a 
            JOIN users u ON a.uploaded_by = u.id 
            WHERE a.status = '$status' 
            ORDER BY a.created_at DESC";
    
    $result = mysqli_query($id_conexiune, $sql);
    $apkFiles = array();
    
    while ($row = mysqli_fetch_assoc($result)) {
        $apkFiles[] = $row;
    }
    
    return $apkFiles;
}

function getApkFileById($id) {
    global $id_conexiune;
    
    $id = (int) $id;
    
    $sql = "SELECT a.*, u.username as uploaded_by_username 
            FROM apk_files a 
            JOIN users u ON a.uploaded_by = u.id 
            WHERE a.id = $id";
    
    $result = mysqli_query($id_conexiune, $sql);
    
    if ($row = mysqli_fetch_assoc($result)) {
        return $row;
    }
    
    return null;
}

function downloadApk($apkId, $userId) {
    global $id_conexiune;
    
    $apkId = (int) $apkId;
    $userId = (int) $userId;
    
    // Obținem informațiile despre fișierul APK
    $apk = getApkFileById($apkId);
    
    if (!$apk || $apk['status'] != 'active') {
        return array('success' => false, 'message' => 'Fișierul APK nu a fost găsit sau nu este disponibil pentru descărcare.');
    }
    
    // Înregistrăm descărcarea în log
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    
    $sql = "INSERT INTO download_logs (user_id, apk_id, ip_address, user_agent) 
            VALUES ($userId, $apkId, '$ipAddress', '$userAgent')";
    
    mysqli_query($id_conexiune, $sql);
    
    // Incrementăm numărul de descărcări
    $sql = "UPDATE apk_files SET downloads = downloads + 1 WHERE id = $apkId";
    mysqli_query($id_conexiune, $sql);
    
    return array(
        'success' => true, 
        'file_path' => $apk['file_path'], 
        'file_name' => $apk['name'] . '_v' . $apk['version'] . '.apk'
    );
}

function getUserDownloadHistory($userId) {
    global $id_conexiune;
    
    $userId = (int) $userId;
    
    $sql = "SELECT dl.*, a.name as apk_name, a.version as apk_version 
            FROM download_logs dl 
            JOIN apk_files a ON dl.apk_id = a.id 
            WHERE dl.user_id = $userId 
            ORDER BY dl.downloaded_at DESC";
    
    $result = mysqli_query($id_conexiune, $sql);
    $downloads = array();
    
    while ($row = mysqli_fetch_assoc($result)) {
        $downloads[] = $row;
    }
    
    return $downloads;
}
