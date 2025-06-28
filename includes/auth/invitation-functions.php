<?php
function validateInvitationCode($code) {
    global $id_conexiune;
    
    if (empty($code)) {
        return false;
    }
    
    $code = mysqli_real_escape_string($id_conexiune, $code);
    $sql = "SELECT * FROM invitations WHERE code = '$code' AND status = 'active'";
    $result = mysqli_query($id_conexiune, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return false;
}

function registerUser($username, $password, $email, $fullName, $invitationCode) {
    global $id_conexiune;
    
    // Verifică dacă invitația este validă
    $invitation = validateInvitationCode($invitationCode);
    if (!$invitation) {
        return array('success' => false, 'message' => 'Codul de invitație este invalid sau a fost deja utilizat.');
    }
    
    // Verifică dacă username-ul sau email-ul sunt deja folosite
    $username = mysqli_real_escape_string($id_conexiune, $username);
    $email = mysqli_real_escape_string($id_conexiune, $email);
    
    $sql = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
    $result = mysqli_query($id_conexiune, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        return array('success' => false, 'message' => 'Username-ul sau email-ul sunt deja utilizate.');
    }
    
    // Creează contul utilizatorului
    $password = mysqli_real_escape_string($id_conexiune, $password);
    $fullName = mysqli_real_escape_string($id_conexiune, $fullName);
    
    $sql = "INSERT INTO users (username, password, email, full_name, invitation_code) 
            VALUES ('$username', MD5('$password'), '$email', '$fullName', '$invitationCode')";
    
    if (mysqli_query($id_conexiune, $sql)) {
        // Actualizează statusul invitației
        $userId = mysqli_insert_id($id_conexiune);
        $invitationId = $invitation['id'];
        
        $sql = "UPDATE invitations SET 
                status = 'used', 
                used_at = NOW(), 
                used_by = $userId 
                WHERE id = $invitationId";
        
        mysqli_query($id_conexiune, $sql);
        
        return array('success' => true, 'message' => 'Contul a fost creat cu succes!');
    } else {
        return array('success' => false, 'message' => 'A apărut o eroare la crearea contului: ' . mysqli_error($id_conexiune));
    }
}

function generateInvitationCode($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';
    
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $code;
}

function createInvitation($email, $createdBy) {
    global $id_conexiune;
    
    $code = generateInvitationCode();
    $email = mysqli_real_escape_string($id_conexiune, $email);
    $createdBy = (int) $createdBy;
    
    $sql = "INSERT INTO invitations (code, email, created_by) 
            VALUES ('$code', '$email', $createdBy)";
    
    if (mysqli_query($id_conexiune, $sql)) {
        return array('success' => true, 'code' => $code);
    } else {
        return array('success' => false, 'message' => 'A apărut o eroare la generarea invitației: ' . mysqli_error($id_conexiune));
    }
}

function getInvitationsByUser($userId) {
    global $id_conexiune;
    
    $userId = (int) $userId;
    
    $sql = "SELECT i.*, u.username as used_by_username 
            FROM invitations i 
            LEFT JOIN users u ON i.used_by = u.id 
            WHERE i.created_by = $userId 
            ORDER BY i.created_at DESC";
    
    $result = mysqli_query($id_conexiune, $sql);
    $invitations = array();
    
    while ($row = mysqli_fetch_assoc($result)) {
        $invitations[] = $row;
    }
    
    return $invitations;
}

function isAdmin() {
    return isset($_SESSION['user_id']) && isUserAdmin($_SESSION['user_id']);
}

function isUserAdmin($userId) {
    global $id_conexiune;
    
    $userId = (int) $userId;
    
    $sql = "SELECT is_admin FROM users WHERE id = $userId";
    $result = mysqli_query($id_conexiune, $sql);
    
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['is_admin'] == 1;
    }
    
    return false;
}
