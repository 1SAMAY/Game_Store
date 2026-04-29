<?php
/**
 * Game Store database bootstrap page
 *
 * Visit this page once on XAMPP to create the database, tables, admin user,
 * and starter games. It is safe to re-run because it uses IF NOT EXISTS /
 * INSERT IGNORE / upserts.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$user = 'root';
$pass = '';
$dbName = 'game_store';

function esc($conn, $value) {
    return $conn->real_escape_string($value);
}

function column_exists(mysqli $conn, string $table, string $column): bool {
    $table = $conn->real_escape_string($table);
    $column = $conn->real_escape_string($column);
    $sql = "SHOW COLUMNS FROM `$table` LIKE '$column'";
    $result = $conn->query($sql);
    return $result && $result->num_rows > 0;
}

function index_exists(mysqli $conn, string $table, string $index): bool {
    $table = $conn->real_escape_string($table);
    $index = $conn->real_escape_string($index);
    $sql = "SHOW INDEX FROM `$table` WHERE Key_name = '$index'";
    $result = $conn->query($sql);
    return $result && $result->num_rows > 0;
}

$messages = [];
$errors = [];

$server = new mysqli($host, $user, $pass);
if ($server->connect_error) {
    die('Database server connection failed: ' . $server->connect_error);
}

$server->set_charset('utf8mb4');

if (!$server->query("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
    die('Failed to create database: ' . $server->error);
}

$conn = new mysqli($host, $user, $pass, $dbName);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');

$schemaSql = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        username VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) NOT NULL DEFAULT 'user',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_username (username),
        UNIQUE KEY unique_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    "CREATE TABLE IF NOT EXISTS games (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        image TEXT,
        description TEXT,
        category VARCHAR(255),
        price VARCHAR(50) DEFAULT 'Free',
        media_url TEXT,
        min_requirement TEXT,
        max_requirement TEXT,
        story LONGTEXT,
        rating DECIMAL(3,1) DEFAULT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY unique_games_title (title)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    "CREATE TABLE IF NOT EXISTS library (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        game_id INT UNSIGNED NOT NULL,
        added_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_library_game (game_id),
        CONSTRAINT fk_library_game
            FOREIGN KEY (game_id) REFERENCES games (id)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    "CREATE TABLE IF NOT EXISTS wishlist (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        game_id INT UNSIGNED NOT NULL,
        added_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_wishlist_game (game_id),
        CONSTRAINT fk_wishlist_game
            FOREIGN KEY (game_id) REFERENCES games (id)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

foreach ($schemaSql as $sql) {
    if (!$conn->query($sql)) {
      $errors[] = $conn->error;
    }
}

// User profile extras
$userColumns = [
    "display_name" => "ALTER TABLE users ADD COLUMN display_name VARCHAR(120) NULL AFTER username",
    "avatar_url" => "ALTER TABLE users ADD COLUMN avatar_url VARCHAR(255) NULL AFTER display_name",
    "bio" => "ALTER TABLE users ADD COLUMN bio TEXT NULL AFTER avatar_url",
    "favorite_genre" => "ALTER TABLE users ADD COLUMN favorite_genre VARCHAR(120) NULL AFTER bio",
    "theme_preference" => "ALTER TABLE users ADD COLUMN theme_preference VARCHAR(20) NOT NULL DEFAULT 'dark' AFTER favorite_genre",
    "email_verified_at" => "ALTER TABLE users ADD COLUMN email_verified_at TIMESTAMP NULL DEFAULT NULL AFTER created_at"
];
foreach ($userColumns as $column => $sql) {
    if (!column_exists($conn, 'users', $column)) {
        if (!$conn->query($sql)) {
            $errors[] = $conn->error;
        }
    }
}

// Make library / wishlist user-specific
foreach (['library', 'wishlist'] as $table) {
    if (!column_exists($conn, $table, 'user_id')) {
        if (!$conn->query("ALTER TABLE `$table` ADD COLUMN user_id INT UNSIGNED NOT NULL DEFAULT 1 AFTER id")) {
            $errors[] = $conn->error;
        }
    }

    if (!index_exists($conn, $table, 'unique_user_game')) {
        if (!index_exists($conn, $table, 'idx_game_user')) {
            if (!$conn->query("ALTER TABLE `$table` ADD INDEX idx_game_user (game_id, user_id)")) {
                $errors[] = $conn->error;
            }
        }
        if (index_exists($conn, $table, $table === 'library' ? 'unique_library_game' : 'unique_wishlist_game')) {
            $oldIndex = $table === 'library' ? 'unique_library_game' : 'unique_wishlist_game';
            if (!$conn->query("ALTER TABLE `$table` DROP INDEX `$oldIndex`")) {
                $errors[] = $conn->error;
            }
        }
        if (!$conn->query("ALTER TABLE `$table` ADD UNIQUE KEY unique_user_game (user_id, game_id)")) {
            $errors[] = $conn->error;
        }
    }

    if (!$conn->query("UPDATE `$table` SET user_id = 1 WHERE user_id IS NULL OR user_id = 0")) {
        $errors[] = $conn->error;
    }
}

// Recent activity, reviews and collections
$extraTables = [
    "CREATE TABLE IF NOT EXISTS recently_viewed (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id INT UNSIGNED NOT NULL,
        game_id INT UNSIGNED NOT NULL,
        viewed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_recent_game (user_id, game_id),
        CONSTRAINT fk_recent_user FOREIGN KEY (user_id) REFERENCES users (id)
            ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT fk_recent_game FOREIGN KEY (game_id) REFERENCES games (id)
            ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    "CREATE TABLE IF NOT EXISTS reviews (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        game_id INT UNSIGNED NOT NULL,
        user_id INT UNSIGNED NOT NULL,
        rating TINYINT UNSIGNED NOT NULL,
        review_text TEXT NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_game_review (game_id, user_id),
        CONSTRAINT fk_review_user FOREIGN KEY (user_id) REFERENCES users (id)
            ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT fk_review_game FOREIGN KEY (game_id) REFERENCES games (id)
            ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    "CREATE TABLE IF NOT EXISTS collections (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id INT UNSIGNED NOT NULL,
        name VARCHAR(120) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_collection_name (user_id, name),
        CONSTRAINT fk_collection_user FOREIGN KEY (user_id) REFERENCES users (id)
            ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    "CREATE TABLE IF NOT EXISTS collection_items (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        collection_id INT UNSIGNED NOT NULL,
        game_id INT UNSIGNED NOT NULL,
        added_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_collection_game (collection_id, game_id),
        CONSTRAINT fk_collection_item_collection FOREIGN KEY (collection_id) REFERENCES collections (id)
            ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT fk_collection_item_game FOREIGN KEY (game_id) REFERENCES games (id)
            ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    "CREATE TABLE IF NOT EXISTS auth_tokens (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id INT UNSIGNED NOT NULL,
        purpose VARCHAR(40) NOT NULL,
        token_hash CHAR(64) NOT NULL,
        expires_at DATETIME NOT NULL,
        used_at DATETIME NULL DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_token_hash (token_hash),
        KEY idx_token_user_purpose (user_id, purpose),
        CONSTRAINT fk_token_user FOREIGN KEY (user_id) REFERENCES users (id)
            ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    "CREATE TABLE IF NOT EXISTS notifications (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id INT UNSIGNED NOT NULL,
        title VARCHAR(160) NOT NULL,
        message TEXT NOT NULL,
        type VARCHAR(20) NOT NULL DEFAULT 'info',
        link VARCHAR(255) DEFAULT NULL,
        is_read TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_notifications_user_read (user_id, is_read),
        CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users (id)
            ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];
foreach ($extraTables as $sql) {
    if (!$conn->query($sql)) {
        $errors[] = $conn->error;
    }
}

$adminPasswordHash = password_hash('admin123', PASSWORD_DEFAULT);
$adminEmail = 'admin@gamestore.local';
$adminUser = 'admin';
$stmt = $conn->prepare(
    "INSERT INTO users (username, email, password, role)
     VALUES (?, ?, ?, 'admin')
     ON DUPLICATE KEY UPDATE role = VALUES(role)"
);
if ($stmt) {
    $stmt->bind_param('sss', $adminUser, $adminEmail, $adminPasswordHash);
    if (!$stmt->execute()) {
        $errors[] = $stmt->error;
    }
    $stmt->close();
} else {
    $errors[] = $conn->error;
}

$conn->query("UPDATE users SET email_verified_at = COALESCE(email_verified_at, NOW()) WHERE email = 'admin@gamestore.local'");

$games = [
    [1,  'Red Dead Redemption 2',   'images/RDR2.jpg',               'Outlaws for life in the dying days of the wild west.', 'Action / Adventure', 'Free', 'Lead Arthur Morgan and the Van der Linde gang across a changing frontier.', 4.9],
    [2,  'Black Myth: Wukong',      'images/Black Myth Wukong.jpeg', 'Unleash your legend in the mythical world of Sun Wukong.', 'Action / RPG', 'Free', 'A mythic action RPG inspired by Journey to the West.', 4.8],
    [3,  'Grand Theft Auto V',      'images/Gta V.jpg',              'Build an empire to stand the test of time.', 'Action / Adventure', 'Free', 'Explore Los Santos in a modern open-world crime epic.', 4.7],
    [4,  'VALORANT',                'icons/Valorant.png',            'A 5v5 character-based tactical FPS.', 'Shooter / Tactical', 'Free', 'Compete in precise team-based firefights.', 4.6],
    [5,  'Far Cry 6',               'images/Far Cry 6.jpg',          'Fight against a modern-day dictatorship in the tropical island of Yara.', 'Shooter / Open World', 'Free', 'A guerrilla revolution on the island of Yara.', 4.2],
    [6,  'ARK',                     'images/ARK.jpeg',               'Survive, tame, and build in a prehistoric world.', 'Survival / Adventure', 'Free', 'Survive on an island full of dinosaurs.', 4.0],
    [7,  'Uncharted 4',             'images/Uncharted 4.jpg',        'A globe-trotting adventure of treasure and betrayal.', 'Action / Adventure', 'Free', 'Nathan Drake returns for one final hunt.', 4.5],
    [8,  'Elden Ring',              'images/Elden-Ring.jpg',         'Explore a vast fantasy realm of danger and discovery.', 'Action / RPG', 'Free', 'Traverse the Lands Between and forge your path.', 4.9],
    [9,  'Dead by Daylight',        'images/Dead-by-Daylight.png',   'Survive the horror or become the hunter.', 'Horror / Multiplayer', 'Free', 'A multiplayer asymmetrical horror experience.', 4.1],
    [10, 'GTA V Enhanced',          'icons/Grand Theft Auto V.png',  'Enhanced edition of the modern crime classic.', 'Action / Adventure', 'Free', 'A refreshed version of Grand Theft Auto V.', 4.7],
    [11, 'F1 25',                   'icons/F125.JPEG',               'High-speed racing with the latest Formula 1 thrills.', 'Racing / Sports', 'Free', 'Compete on the world\'s fastest tracks.', 4.3],
    [12, 'Stellar Blade',           'icons/stellar.png',             'Fast-paced sci-fi combat with cinematic style.', 'Action / RPG', 'Free', 'Battle across a devastated future Earth.', 4.4],
    [13, 'Fortnite',                'images/Fortnite.jpg',          'A massive battle royale with constant updates.', 'Shooter / Battle Royale', 'Free', 'Drop in and survive to be the last one standing.', 4.6],
    [14, 'Rocket League',           'icons/ROCKET.png',              'Soccer meets rocket-powered cars.', 'Sports / Racing', 'Free', 'Score goals at high speed with your team.', 4.5],
    [15, 'Football Manager 2024',   'icons/FM24.png',                'Take control of a club and shape its future.', 'Simulation / Sports', 'Free', 'Manage tactics, transfers, and glory.', 4.2],
    [16, 'Genshin Impact',          'icons/GEN.PNG',                 'Explore a vibrant fantasy world full of elemental magic.', 'Action / RPG', 'Free', 'Embark on a journey across Teyvat.', 4.4],
    [17, 'MotoGP 25',               'icons/motogp25.png',            'The next chapter of elite motorcycle racing.', 'Racing / Sports', 'Free', 'Race the official MotoGP season.', 4.1],
    [18, 'Dying Light: The Beast',  'icons/DYING.png',               'Survive the undead in a brutal open world.', 'Horror / Survival', 'Free', 'Parkour, survival, and zombies collide.', 4.2],
    [19, 'Tides of Annihilation',   'icons/TIDES.png',               'A cinematic action journey through broken worlds.', 'Action / Adventure', 'Free', 'Fight through a mythic world under siege.', 4.0],
    [20, 'MONGIL: STAR DIVE',       'icons/MONGIL.png',              'A fantasy adventure with team-based combat.', 'RPG / Adventure', 'Free', 'A colorful adventure in a living fantasy universe.', 4.0],
    [21, 'Resident Evil Requiem',   'icons/RDE.png',                 'Classic survival horror with modern intensity.', 'Horror / Survival', 'Free', 'Face an all-new nightmare.', 4.5],
];

foreach ($games as $game) {
    [$id, $title, $image, $description, $category, $price, $story, $rating] = $game;
    $mediaUrl = '';
    $minReq = '';
    $maxReq = '';
    $stmt = $conn->prepare(
        "INSERT INTO games (id, title, image, description, category, price, media_url, min_requirement, max_requirement, story, rating)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            title = VALUES(title),
            image = VALUES(image),
            description = VALUES(description),
            category = VALUES(category),
            price = VALUES(price),
            media_url = VALUES(media_url),
            min_requirement = VALUES(min_requirement),
            max_requirement = VALUES(max_requirement),
            story = VALUES(story),
            rating = VALUES(rating)"
    );
    if ($stmt) {
        $stmt->bind_param('isssssssssd', $id, $title, $image, $description, $category, $price, $mediaUrl, $minReq, $maxReq, $story, $rating);
        if (!$stmt->execute()) {
            $errors[] = $stmt->error;
        }
        $stmt->close();
    } else {
        $errors[] = $conn->error;
    }
}

$messages[] = 'Database created or updated successfully.';
$messages[] = 'Admin account: admin / admin123';
$messages[] = 'Seeded ' . count($games) . ' games.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Game Store Database Setup</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      min-height: 100vh;
      background: linear-gradient(135deg, #090909 0%, #131313 50%, #1f2937 100%);
      color: #fff;
      font-family: "Segoe UI", sans-serif;
    }
    .panel {
      max-width: 980px;
      margin: 48px auto;
      background: rgba(255,255,255,0.06);
      border: 1px solid rgba(255,255,255,0.12);
      border-radius: 24px;
      padding: 32px;
      backdrop-filter: blur(16px);
      box-shadow: 0 24px 80px rgba(0,0,0,0.45);
    }
    .badge-soft {
      background: rgba(0, 207, 255, 0.16);
      border: 1px solid rgba(0, 207, 255, 0.35);
      color: #d7f7ff;
    }
    code {
      color: #9ef0ff;
    }
  </style>
</head>
<body>
  <div class="panel">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <div>
        <h1 class="mb-2">Game Store Database Setup</h1>
        <p class="mb-0 text-secondary">Run this once in XAMPP to create the database and seed the project data.</p>
      </div>
      <span class="badge badge-soft rounded-pill px-3 py-2">game_store</span>
    </div>

    <?php if ($errors): ?>
      <div class="alert alert-warning">
        <strong>Completed with warnings.</strong>
        <ul class="mb-0">
          <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php else: ?>
      <div class="alert alert-success">Setup finished successfully.</div>
    <?php endif; ?>

    <div class="row g-4">
      <div class="col-md-6">
        <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.04);">
          <h5 class="mb-3">What this created</h5>
          <ul class="mb-0">
            <?php foreach ($messages as $message): ?>
              <li><?= htmlspecialchars($message) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
      <div class="col-md-6">
        <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.04);">
          <h5 class="mb-3">Next steps</h5>
          <ol class="mb-0">
            <li>Open <code>index.php</code> to verify the storefront.</li>
            <li>Use <code>login.php</code> to register a user.</li>
            <li>Use <code>admin/login.php</code> with <code>admin / admin123</code>.</li>
            <li>Delete this setup page before going live if you do not want it accessible.</li>
          </ol>
        </div>
      </div>
    </div>

    <div class="mt-4">
      <a href="index.php" class="btn btn-info me-2">Open Store</a>
      <a href="admin/login.php" class="btn btn-outline-light">Admin Login</a>
    </div>
  </div>
</body>
</html>
