<?php
require_once dirname(__DIR__) . '/db.php';

// Helper: Check admin session
function is_admin() {
    // session_start();  // REMOVE THIS LINE!
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin';
}

function require_admin() {
    if (!is_admin()) {
        header('Location: login.php');
        exit();
    }
}
?>
