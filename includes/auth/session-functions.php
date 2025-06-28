<?php
function isLogged() {
    return isset($_SESSION['logat']) && $_SESSION['logat'] == TRUE;
}

function getLoggedUser() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : NULL;
}