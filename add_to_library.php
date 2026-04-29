<?php
require_once "app_helpers.php";
require_once "db.php";

app_require_login();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['game_id'])) {
  $game_id = intval($_POST['game_id']);
  $user_id = app_current_user_id();

  // Check if the game exists
  $check_query = "SELECT id FROM games WHERE id = ?";
  $check_stmt = $conn->prepare($check_query);
  $check_stmt->bind_param("i", $game_id);
  $check_stmt->execute();
  $check_result = $check_stmt->get_result();

  if (mysqli_num_rows($check_result) > 0) {
    // Optional: check if already in library
    $check_lib = $conn->prepare("SELECT id FROM library WHERE user_id = ? AND game_id = ?");
    $check_lib->bind_param("ii", $user_id, $game_id);
    $check_lib->execute();
    $check_lib_result = $check_lib->get_result();
    if (mysqli_num_rows($check_lib_result) == 0) {
      // Insert into library
      $insert_query = $conn->prepare("INSERT INTO library (user_id, game_id) VALUES (?, ?)");
      $insert_query->bind_param("ii", $user_id, $game_id);
      $insert_query->execute();
      app_add_notification($conn, $user_id, 'Library updated', 'A game was added to your library.', 'success', 'library.php');
    }
    app_flash('success', 'Game added to your library.');
    header("Location: library.php");
    exit;
  } else {
    app_flash('warning', 'Game not found in database.');
    header("Location: index.php");
    exit;
  }
} else {
  app_flash('warning', 'Invalid request.');
  header("Location: index.php");
  exit;
}
?>
