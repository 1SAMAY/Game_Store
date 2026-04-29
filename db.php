<?php
$host = "localhost";         // or "127.0.0.1"
$username = "root";          // default username for XAMPP
$password = "";              // default password for XAMPP is empty
$database = "game_store";    // replace with your actual database name

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
