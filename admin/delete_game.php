<?php
session_start();
require_once 'db.php';
require_admin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id > 0) {
    $conn->query('DELETE FROM library WHERE game_id = ' . $id);
    $conn->query('DELETE FROM wishlist WHERE game_id = ' . $id);
    $conn->query('DELETE FROM games WHERE id = ' . $id);
}
header('Location: dashboard.php?msg=deleted');
exit();
?>