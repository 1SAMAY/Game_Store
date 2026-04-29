<?php
require_once "app_helpers.php";
require_once "db.php";

app_require_login();

if (isset($_POST['game_id'])) {
    $game_id = intval($_POST['game_id']);
    $user_id = app_current_user_id();

    $check = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND game_id = ?");
    $check->bind_param("ii", $user_id, $game_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows == 0) {
        $insert = $conn->prepare("INSERT INTO wishlist (user_id, game_id) VALUES (?, ?)");
        $insert->bind_param("ii", $user_id, $game_id);
        $insert->execute();
        $insert->close();
        app_add_notification($conn, $user_id, 'Wishlist updated', 'A game was added to your wishlist.', 'info', 'wishlist.php');
    }
    $check->close();
}
app_flash('success', 'Wishlist updated.');
header("Location: wishlist.php");
exit();
?>
