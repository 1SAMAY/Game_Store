<?php
include 'db.php';
require_once 'app_helpers.php';

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function renderSpotlightCard(array $game): void
{
    $id = (int) $game['id'];
    $title = e($game['title']);
    $image = e($game['image']);
    ?>
    <div class="game-card">
      <form method="POST" action="add_to_wishlist.php" style="position:absolute; top:12px; right:12px; z-index:2;">
        <input type="hidden" name="game_id" value="<?= $id ?>">
        <input type="hidden" name="title" value="<?= $title ?>">
        <input type="hidden" name="image" value="<?= $image ?>">
        <button type="submit" class="wishlist-btn" title="Add to Wishlist" aria-label="Add <?= $title ?> to wishlist">
          <i class="fa-solid fa-heart"></i>
        </button>
      </form>

      <a href="view_game.php?id=<?= $id ?>">
        <img src="<?= $image ?>" alt="<?= $title ?>" class="game-card-image">
      </a>

      <div class="game-card-body">
        <p class="card-kicker">Base Game</p>
        <p class="card-title"><?= $title ?></p>
        <div class="card-actions">
          <form method="POST" action="add_to_library.php">
            <input type="hidden" name="game_id" value="<?= $id ?>">
            <button type="submit" class="free-btn">FREE</button>
          </form>
        </div>
      </div>
    </div>
    <?php
}

function renderCompactItem(array $game): void
{
    $id = (int) $game['id'];
    $title = e($game['title']);
    $image = e($game['image']);
    ?>
    <div class="game-item">
      <form method="POST" action="add_to_wishlist.php" style="position:absolute; top:10px; right:10px; z-index:2;">
        <input type="hidden" name="game_id" value="<?= $id ?>">
        <input type="hidden" name="title" value="<?= $title ?>">
        <input type="hidden" name="image" value="<?= $image ?>">
        <button type="submit" class="wishlist-btn" title="Add to Wishlist" aria-label="Add <?= $title ?> to wishlist">
          <i class="fa-solid fa-heart"></i>
        </button>
      </form>

      <a href="view_game.php?id=<?= $id ?>">
        <img src="<?= $image ?>" alt="<?= $title ?>">
      </a>

      <div class="info">
        <p class="title"><?= $title ?></p>
        <p class="price">Free</p>
        <form method="POST" action="add_to_library.php">
          <input type="hidden" name="game_id" value="<?= $id ?>">
          <button type="submit" class="game-btn">FREE</button>
        </form>
      </div>
    </div>
    <?php
}

$userName = $_SESSION['user'] ?? null;
$unreadCount = $userName && isset($_SESSION['user_id']) ? app_unread_notification_count($conn, (int) $_SESSION['user_id']) : 0;
$flash = app_take_flash();

$spotlightGames = [
    ['id' => 5, 'title' => 'Far Cry 6', 'image' => 'images/Far Cry 6.jpg'],
    ['id' => 6, 'title' => 'ARK', 'image' => 'images/ARK.jpeg'],
    ['id' => 7, 'title' => 'Uncharted 4', 'image' => 'images/Uncharted 4.jpg'],
    ['id' => 8, 'title' => 'Elden Ring', 'image' => 'images/Elden-Ring.jpg'],
];

$topFree = [
    ['id' => 9, 'title' => 'Dead by Daylight', 'image' => 'images/Dead-by-Daylight.png'],
    ['id' => 10, 'title' => 'GTA V Enhanced', 'image' => 'icons/Grand Theft Auto V.png'],
    ['id' => 2, 'title' => 'Black Myth: Wukong', 'image' => 'icons/Black Myth.png'],
    ['id' => 11, 'title' => 'F1 25', 'image' => 'icons/F125.JPEG'],
    ['id' => 12, 'title' => 'Stellar Blade', 'image' => 'icons/stellar.png'],
];

$mostPlayed = [
    ['id' => 13, 'title' => 'Fortnite', 'image' => 'images/Fortnite.jpg'],
    ['id' => 14, 'title' => 'Rocket League', 'image' => 'icons/ROCKET.png'],
    ['id' => 4, 'title' => 'VALORANT', 'image' => 'icons/Valorant.png'],
    ['id' => 15, 'title' => 'Football Manager 2024', 'image' => 'icons/FM24.png'],
    ['id' => 16, 'title' => 'Genshin Impact', 'image' => 'icons/GEN.PNG'],
];

$upcoming = [
    ['id' => 17, 'title' => 'MotoGP 25', 'image' => 'icons/motogp25.png'],
    ['id' => 18, 'title' => 'Dying Light: The Beast', 'image' => 'icons/DYING.png'],
    ['id' => 19, 'title' => 'Tides of Annihilation', 'image' => 'icons/TIDES.png'],
    ['id' => 20, 'title' => 'MONGIL: STAR DIVE', 'image' => 'icons/MONGIL.png'],
    ['id' => 21, 'title' => 'Resident Evil Requiem', 'image' => 'icons/RDE.png'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Game Store</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer">
  <link rel="stylesheet" href="style.css">
  <script src="theme.js" defer></script>
  <script src="scripts.js" defer></script>
</head>
<body class="site-shell">

<nav class="navbar navbar-expand-lg navbar-dark sticky-top" style="background: rgba(12, 16, 20, 0.78); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);">
  <div class="container-fluid px-3 px-lg-4">
    <a href="index.php" class="d-flex align-items-center gap-3 text-decoration-none">
      <img src="https://img.icons8.com/?size=100&id=bCP28brs5BYg&format=png&color=000000" alt="Game Store" class="logo">
      <span class="fw-semibold text-white">Game Store</span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav me-auto mb-3 mb-lg-0 mt-3 mt-lg-0 nav-buttons">
        <li class="nav-item"><a href="index.php" class="btn nav-btn active">Home</a></li>
        <li class="nav-item"><a href="browse.php" class="btn nav-btn">Browse</a></li>
        <li class="nav-item"><a href="library.php" class="btn nav-btn">Library</a></li>
        <li class="nav-item"><a href="wishlist.php" class="btn nav-btn">Wishlist</a></li>
        <li class="nav-item"><a href="collections.php" class="btn nav-btn">Collections</a></li>
      </ul>

      <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center gap-3">
        <button type="button" class="btn btn-outline-light rounded-pill px-3" data-theme-toggle>Light mode</button>
        <form class="d-flex" role="search" method="GET" action="search.php">
          <div class="input-group search-bar">
            <span class="input-group-text">
              <i class="fa-solid fa-magnifying-glass"></i>
            </span>
            <input type="search" name="query" class="form-control search-input" placeholder="Search store" aria-label="Search store">
          </div>
        </form>

        <div class="d-flex justify-content-lg-end align-items-center gap-2">
          <?php if ($userName): ?>
            <a href="notifications.php" class="btn btn-outline-light rounded-pill px-3 position-relative">
              <i class="fa-regular fa-bell"></i>
              <?php if ($unreadCount > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= (int) $unreadCount ?></span>
              <?php endif; ?>
            </a>
          <?php endif; ?>
          <?php if ($userName): ?>
            <div class="dropdown">
              <button class="btn btn-outline-light rounded-pill px-4 dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="icons/logins.png" alt="User" style="height: 22px;">
                <span><?= e($userName) ?></span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                <li><a class="dropdown-item" href="browse.php">Browse</a></li>
                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
              </ul>
            </div>
          <?php else: ?>
            <a href="login.php" class="btn btn-outline-light rounded-pill px-4 d-flex align-items-center gap-2">
              <img src="icons/logins.png" alt="Login" style="height: 22px;">
              <span>Login</span>
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</nav>

<main class="container-fluid hero-section px-3 px-lg-4">
  <?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?> my-3"><?= e($flash['message']) ?></div>
  <?php endif; ?>
  <div class="row g-4 align-items-start">
    <div class="col-lg-9">
      <div class="featured-banner">
        <img id="banner-img" src="images/RDR2.jpg" class="fade-in" alt="Featured game banner">
        <div class="featured-overlay">
          <p class="hero-eyebrow">Featured Game</p>
          <h1 id="game-title" class="hero-title">RED DEAD REDEMPTION 2</h1>
          <p id="game-desc" class="hero-desc">Outlaws for life in the dying days of the wild west.</p>
          <div class="hero-meta">
            <span class="pill">Free</span>
            <span class="pill ghost">Live Spotlight</span>
          </div>
        </div>
      </div>
    </div>

    <aside class="col-lg-3">
      <div class="sidebar-stack">
        <button type="button" class="game-bar active" data-game="rdr" onclick="showGame('rdr', this)">
          <img src="icons/RDR.jpg" class="game-icon" alt="Red Dead Redemption 2">
          <span>Red Dead Redemption 2</span>
        </button>

        <button type="button" class="game-bar" data-game="blackmyth" onclick="showGame('blackmyth', this)">
          <img src="icons/Black Myth.png" class="game-icon" alt="Black Myth Wukong">
          <span>Black Myth: Wukong</span>
        </button>

        <button type="button" class="game-bar" data-game="gta" onclick="showGame('gta', this)">
          <img src="icons/Grand Theft Auto V.png" class="game-icon" alt="Grand Theft Auto V">
          <span>Grand Theft Auto V</span>
        </button>

        <button type="button" class="game-bar" data-game="valorant" onclick="showGame('valorant', this)">
          <img src="icons/Valorant.png" class="game-icon" alt="Valorant">
          <span>Valorant</span>
        </button>
      </div>
    </aside>
  </div>

  <section class="discover-section">
    <h2>Discover Something New</h2>
    <div class="game-row">
      <?php foreach ($spotlightGames as $game) { renderSpotlightCard($game); } ?>
    </div>
  </section>

  <section class="collection-grid">
    <div class="collection-column">
      <h4>Top Free &rarr;</h4>
      <?php foreach ($topFree as $game) { renderCompactItem($game); } ?>
    </div>

    <div class="collection-column">
      <h4>Most Played &rarr;</h4>
      <?php foreach ($mostPlayed as $game) { renderCompactItem($game); } ?>
    </div>

    <div class="collection-column">
      <h4>Top Upcoming Wishlisted &rarr;</h4>
      <?php foreach ($upcoming as $game) { renderCompactItem($game); } ?>
    </div>
  </section>

  <section class="coming-soon-box">
    <div class="coming-soon-image">
      <img src="images/GTA 6.jpg" alt="GTA VI">
    </div>

    <div class="coming-soon-copy">
      <h3>GTA VI</h3>
      <p>
        The long-awaited return of Rockstar Games' blockbuster crime saga is almost here.
        GTA VI brings a massive open world, dynamic storytelling, and next-gen visuals.
      </p>
      <button class="coming-btn" disabled>Coming Soon</button>
    </div>
  </section>
</main>

<footer class="custom-footer">
  <div class="footer-columns">
    <div class="footer-section">
      <h4>Games</h4>
      <ul>
        <li><a href="https://www.fortnite.com" target="_blank" rel="noopener noreferrer">Fortnite</a></li>
        <li><a href="https://www.fallguys.com" target="_blank" rel="noopener noreferrer">Fall Guys</a></li>
        <li><a href="https://www.rocketleague.com" target="_blank" rel="noopener noreferrer">Rocket League</a></li>
        <li><a href="https://www.epicgames.com/unrealtournament" target="_blank" rel="noopener noreferrer">Unreal Tournament</a></li>
        <li><a href="https://apps.apple.com/us/app/infinity-blade/id387428400" target="_blank" rel="noopener noreferrer">Infinity Blade</a></li>
      </ul>
    </div>

    <div class="footer-section">
      <h4>Company</h4>
      <ul>
        <li><a href="privacy.php">Privacy</a></li>
        <li><a href="refund.php">Refund</a></li>
        <li><a href="security.php">Security</a></li>
        <li><a href="terms.php">Terms</a></li>
      </ul>
    </div>

    <div class="footer-section">
      <h4>Follow Us</h4>
      <div class="social-icons">
        <a href="#" aria-label="Facebook"><img src="https://cdn-icons-png.flaticon.com/512/1384/1384005.png" alt="Facebook"></a>
        <a href="#" aria-label="Twitter"><img src="https://cdn-icons-png.flaticon.com/512/733/733579.png" alt="Twitter"></a>
        <a href="#" aria-label="YouTube"><img src="https://cdn-icons-png.flaticon.com/512/1384/1384012.png" alt="YouTube"></a>
        <a href="#" aria-label="Instagram"><img src="https://cdn-icons-png.flaticon.com/512/1384/1384015.png" alt="Instagram"></a>
      </div>
    </div>
  </div>

  <div class="back-to-top">
    <button type="button" onclick="scrollToTop()">Back to top &uarr;</button>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
<script>
  function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
</script>
</body>
</html>
