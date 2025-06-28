<?php
function doLogin($user, $password) {
    global $id_conexiune;
    $logat = FALSE;
    if (isLogged())
        doLogout();

    $sql = sprintf("SELECT * FROM users WHERE username='%s' AND password=md5('%s')",
        mysqli_real_escape_string($id_conexiune, $user),
        mysqli_real_escape_string($id_conexiune, $password));
    if (!($result = mysqli_query($id_conexiune, $sql))) {
        echo('Error: ' . mysqli_error($id_conexiune));
    }
    if ($row = mysqli_fetch_array($result)) {
        $logat = TRUE;
        $_SESSION['user'] = $user;
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_email'] = $row['email'];
        $_SESSION['user_fullname'] = $row['full_name'];
        $_SESSION['is_admin'] = $row['is_admin'];
        $_SESSION['logat'] = TRUE;
    }
    return $logat;
}

function doLogout() {
    unset($_SESSION['user']);
    unset($_SESSION['user_id']);
    unset($_SESSION['user_email']);
    unset($_SESSION['user_fullname']);
    unset($_SESSION['is_admin']);
    unset($_SESSION['logat']);
}