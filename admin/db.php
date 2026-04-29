<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "game_store";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

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
