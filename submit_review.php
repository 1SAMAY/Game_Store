<?php
require_once "app_helpers.php";
require_once "db.php";

app_require_login();

$gameId = isset($_POST['game_id']) ? (int) $_POST['game_id'] : 0;
$rating = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;
$reviewText = trim($_POST['review_text'] ?? '');
$userId = app_current_user_id();

if ($gameId <= 0) {
    app_flash('warning', 'Invalid game selected.');
    header('Location: index.php');
    exit();
}

if ($rating < 1 || $rating > 5) {
    app_flash('warning', 'Please choose a rating between 1 and 5.');
    header('Location: view_game.php?id=' . $gameId);
    exit();
}

if ($reviewText === '') {
    app_flash('warning', 'Please write a short review.');
    header('Location: view_game.php?id=' . $gameId);
    exit();
}

$stmt = $conn->prepare(
    "INSERT INTO reviews (game_id, user_id, rating, review_text)
     VALUES (?, ?, ?, ?)
     ON CONFLICT (game_id, user_id)
     DO UPDATE SET rating = EXCLUDED.rating,
                   review_text = EXCLUDED.review_text,
                   updated_at = CURRENT_TIMESTAMP"
);

if ($stmt) {
    $stmt->bind_param('iiis', $gameId, $userId, $rating, $reviewText);
    $stmt->execute();
    $stmt->close();
    app_flash('success', 'Your review has been saved.');
    app_add_notification($conn, $userId, 'Review saved', 'Your review for this game was saved successfully.', 'success', 'view_game.php?id=' . $gameId);
} else {
    app_flash('warning', 'Could not save review.');
}

header('Location: view_game.php?id=' . $gameId);
exit();
