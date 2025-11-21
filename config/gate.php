<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Allow access to login pages without session
$current_page = basename($_SERVER['PHP_SELF']);
$login_pages = ['login.php', 'login_a.php', 'register.php', 'forgot-password.php'];

if (!in_array($current_page, $login_pages)) {
    if (!isset($_SESSION['role']) && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Location: login_a.php');
        exit;
    }
}
