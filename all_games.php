<?php
// all_games.php
$conn = new mysqli("localhost", "root", "", "game_store");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$added = isset($_GET['added']) ? intval($_GET['added']) : 0;
$exists = isset($_GET['exists']) ? intval($_GET['exists']) : 0;

$sql = "SELECT * FROM games ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>All Games</title>
  <style>
    body {
      background-color: #121212;
      color: #fff;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 24px;
    }

    /* Top bar */
    .btn {
      background: #2c2c2c;
      color: #fff;
      border: 1px solid #444;
      padding: 8px 14px;
      border-radius: 8px;
      text-decoration: none;
      display:inline-block;
      font-weight:600;
      transition: all 0.3s ease, box-shadow 0.3s ease;
    }
    .btn:hover {
      background:#00cfff;
      color:#000;
      transform: scale(1.1) rotate(-2deg);
      box-shadow: 0 0 15px rgba(0,207,255,0.6);
    }
    .btn-dark { background: #000; border: 1px solid #444; }

    .top-bar {
      display:flex;
      gap:12px;
      align-items:center;
      margin-bottom:18px;
    }

    h1 { margin: 0 0 18px 0; font-size: 24px; }

    /* Games grid */
    .games-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
      gap: 20px;
    }

    .game-card {
      background: #1f1f1f;
      border-radius: 12px;
      overflow: hidden;
      position: relative;
      box-shadow: 0 8px 20px rgba(0,0,0,0.4);
      transition: transform .25s ease, box-shadow .25s ease;
      animation: fadeInUp 0.6s ease forwards;
      opacity: 0;
    }
    .game-card:hover {
      transform: translateY(-8px) scale(1.03);
      box-shadow: 0 15px 35px rgba(0,0,0,0.7);
    }

    .game-link {
      text-decoration: none;
      color: inherit;
      display: block;
    }

    .game-card img {
      width: 100%;
      height: 160px;
      object-fit: cover;
      display: block;
      transition: transform .4s ease;
    }
    .game-card:hover img { transform: scale(1.1); }

    .game-info {
      padding: 12px;
      position: relative;
    }
    .game-title { font-size: 16px; margin: 6px 0 2px; font-weight: 700; color: #fff; }
    .game-base { font-size: 12px; color: #a0a0a0; margin: 0 0 8px; }

    /* FREE button */
    .free-btn {
      display: inline-block;
      background: linear-gradient(90deg,#00aaff,#0066ff);
      color: #fff;
      padding: 8px 12px;
      border-radius: 8px;
      border: none;
      cursor: pointer;
      font-weight: 700;
      font-size: 13px;
      transition: all 0.25s ease;
      box-shadow: 0 4px 12px rgba(0,0,0,0.5);
      position: relative;
      overflow: hidden;
      animation: pulseGlow 2s infinite;
    }
    .free-btn:hover {
      transform: scale(1.1) rotate(-1deg);
      box-shadow: 0 6px 16px rgba(0,0,0,0.7);
    }

    @keyframes pulseGlow {
      0%, 100% { box-shadow: 0 0 8px rgba(0,207,255,0.5); }
      50% { box-shadow: 0 0 20px rgba(0,207,255,0.9); }
    }

    /* Floating glow effect */
    .game-card::before {
      content: "";
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(0,153,255,0.15), transparent 70%);
      transform: rotate(25deg);
      opacity: 0;
      transition: opacity .3s ease;
    }
    .game-card:hover::before { opacity: 1; }

    /* Messages */
    .msg {
      display:inline-block;
      padding:8px 12px;
      border-radius:8px;
      margin-left:10px;
      font-weight:600;
      background:#0a6;
      color:#012;
      animation: fadeIn 0.5s ease;
    }
    .msg.warn { background:#ffcc00; color:#222; }

    /* Animations */
    @keyframes fadeInUp {
      from { opacity:0; transform: translateY(20px); }
      to { opacity:1; transform: translateY(0); }
    }
    @keyframes fadeIn { from { opacity:0; } to { opacity:1; } }

    @media (max-width:480px) {
      .game-card img { height: 120px; }
    }

    /* Ripple effect */
    .free-btn .ripple {
      position: absolute;
      width: 60px;
      height: 60px;
      background: rgba(255,255,255,0.5);
      border-radius: 50%;
      transform: scale(0);
      animation: rippleEffect 0.6s linear;
      pointer-events: none;
    }
    @keyframes rippleEffect {
      to { transform: scale(4); opacity: 0; }
    }
  </style>
</head>
<body>

  <div class="top-bar">
    <a href="index.php" class="btn">← Back to Store</a>
    <a href="library.php" class="btn btn-dark">Library</a>
    <a href="wishlist.php" class="btn">Wishlist</a>
    <h1 style="margin-left:12px;">All Games</h1>

    <?php if ($added): ?>
      <div class="msg">Added to library</div>
    <?php elseif ($exists): ?>
      <div class="msg warn">Already in library</div>
    <?php endif; ?>
  </div>

  <div class="games-grid" id="gamesGrid">
    <?php if ($result && $result->num_rows > 0): ?>
      <?php while($g = $result->fetch_assoc()): ?>
        <div class="game-card">
          <!-- Card is clickable -->
          <a class="game-link" href="view_game.php?id=<?= intval($g['id']); ?>">
            <img src="<?= htmlspecialchars($g['image'] ?: 'images/placeholder.png'); ?>" alt="<?= htmlspecialchars($g['title']); ?>">
            <div class="game-info">
              <p class="game-base">Base Game</p>
              <p class="game-title"><?= htmlspecialchars($g['title']); ?></p>
            </div>
          </a>
          <!-- Separate FREE button -->
          <div style="padding: 0 12px 12px;">
            <form method="POST" action="add_to_library.php">
              <input type="hidden" name="game_id" value="<?= intval($g['id']); ?>">
              <button type="submit" class="free-btn">FREE</button>
            </form>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p>No games found.</p>
    <?php endif; ?>
  </div>

<script>
  // Animate game cards one by one (staggered)
  document.addEventListener("DOMContentLoaded", () => {
    const cards = document.querySelectorAll(".game-card");
    cards.forEach((card, i) => {
      card.style.animationDelay = (i * 0.1) + "s";
    });

    // FREE button ripple effect
    document.querySelectorAll(".free-btn").forEach(btn => {
      btn.addEventListener("click", function(e) {
        let ripple = document.createElement("span");
        ripple.classList.add("ripple");
        ripple.style.left = e.offsetX + "px";
        ripple.style.top = e.offsetY + "px";
        this.appendChild(ripple);
        setTimeout(() => ripple.remove(), 600);
      });
    });
  });

  // Card tilt effect
  document.querySelectorAll(".game-card").forEach(card => {
    card.addEventListener("mousemove", (e) => {
      const rect = card.getBoundingClientRect();
      let x = (e.clientX - rect.left) / rect.width - 0.5;
      let y = (e.clientY - rect.top) / rect.height - 0.5;
      card.style.transform = `rotateY(${x * 15}deg) rotateX(${y * -15}deg) scale(1.03)`;
    });
    card.addEventListener("mouseleave", () => {
      card.style.transform = "rotateY(0) rotateX(0) scale(1)";
    });
  });
</script>

</body>
</html>
<?php $conn->close(); ?>
