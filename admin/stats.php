<?php
session_start();
require_once "db.php";
require_admin();

$numUsers = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
$numGames = $conn->query("SELECT COUNT(*) AS c FROM games")->fetch_assoc()['c'];
$numLibs = $conn->query("SELECT COUNT(*) AS c FROM library")->fetch_assoc()['c'];

$topGames = $conn->query("SELECT g.title, COUNT(*) as cnt FROM library l JOIN games g ON l.game_id=g.id GROUP BY g.id ORDER BY cnt DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Stats</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container py-5">
    <h2>Stats</h2>
    <ul class="list-group mb-4">
        <li class="list-group-item">Number of Users: <b><?= $numUsers ?></b></li>
        <li class="list-group-item">Games in Store: <b><?= $numGames ?></b></li>
        <li class="list-group-item">Games in Libraries: <b><?= $numLibs ?></b></li>
    </ul>
    <h4>Most Added Games (Top Sellers)</h4>
    <table class="table table-dark table-striped">
        <thead>
            <tr><th>Game</th><th>Times Added</th></tr>
        </thead>
        <tbody>
            <?php while ($g = $topGames->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($g['title']) ?></td>
                    <td><?= $g['cnt'] ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>