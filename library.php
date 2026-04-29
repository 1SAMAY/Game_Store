<?php
require_once "app_helpers.php";
require_once "db.php";

app_require_login();
$userId = app_current_user_id();

$sql = "SELECT l.game_id, g.* 
        FROM library l 
        JOIN games g ON l.game_id = g.id
        WHERE l.user_id = ?
        ORDER BY l.added_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$flash = app_take_flash();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Library</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="theme.js" defer></script>
  <style>
    body {
      background-color: #1e1e1e;
      color: white;
      font-family: 'Segoe UI', sans-serif;
    }
    h2 { margin-bottom: 20px; }
    .game-row {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
    }
    .game-card {
      background-color: #2a2a2a;
      border-radius: 12px;
      overflow: hidden;
      width: 250px;
      color: white;
      flex-shrink: 0;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .game-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 16px rgba(0,0,0,0.6);
    }
    .game-card img {
      width: 100%;
      height: 150px;
      object-fit: cover;
    }
    .game-info { padding: 15px; }
    .game-title {
      font-size: 1.1rem;
      font-weight: bold;
      margin-bottom: 8px;
    }

    /* Buttons */
    .game-btn {
      padding: 6px 12px;
      font-size: 0.9rem;
      font-weight: bold;
      border: none;
      border-radius: 20px;
      background: linear-gradient(45deg, #00cfff, #00b2e6);
      color: black;
      cursor: pointer;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      margin: 10px auto;
      display: block;
      position: relative;
      overflow: hidden;
    }
    .game-btn::after {
      content: "";
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: linear-gradient(120deg, rgba(255,255,255,0.3) 0%, transparent 60%);
      transform: rotate(25deg);
      animation: shine 2s infinite;
    }
    .game-btn:hover {
      transform: scale(1.05);
      box-shadow: 0 4px 12px rgba(0, 207, 255, 0.6);
    }
    @keyframes shine {
      0% { left: -150%; }
      100% { left: 150%; }
    }

    /* Play button style */
    .play-btn {
      background: linear-gradient(45deg, #00ff99, #00cc77);
      color: black !important;
    }
    .play-btn::after {
      animation: shine 1.5s infinite;
    }

    /* Modal */
    .modal-overlay {
      display: none;
      position: fixed;
      z-index: 1000;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0,0,0,0.7);
      justify-content: center;
      align-items: center;
    }
    .modal-box {
      background: #2a2a2a;
      border-radius: 12px;
      padding: 20px;
      width: 350px;
      text-align: center;
      box-shadow: 0 6px 20px rgba(0,0,0,0.8);
      animation: fadeInUp 0.5s ease;
    }
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .progress-container {
      margin-top: 15px;
      background: #444;
      border-radius: 10px;
      overflow: hidden;
      height: 18px;
    }
    .progress {
      background: linear-gradient(90deg, #00cfff, #00b2e6);
      height: 100%;
      width: 0%;
      transition: width 0.3s ease;
    }
 /* Same as wishlist remove button */
.remove-button {
  position: absolute;
  top: 6px;
  right: 6px;
  background-color: transparent;
  border: none;
  color: #fff;
  font-size: 18px;
  cursor: pointer;
  transition: transform 0.3s ease, color 0.3s ease;
  z-index: 10;
}

.remove-button:hover {
  color: #ff4d4d;
  transform: scale(1.3) rotate(10deg);
}

/* Make sure game-card is relative for positioning */
.game-card {
  position: relative;
}


/* Modal overlay */
.modal {
  display: none;
  position: fixed;
  z-index: 1000;
  left: 0; top: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.7);
  justify-content: center;
  align-items: center;
}

/* Modal box */
.modal-content {
  background: #1e1e1e;
  color: #fff;
  padding: 20px;
  border-radius: 10px;
  text-align: center;
  width: 300px;
  box-shadow: 0 0 20px rgba(0,0,0,0.8);
}

/* Progress bar wrapper */
.progress-bar {
  width: 100%;
  height: 20px;
  background: #333;
  border-radius: 10px;
  overflow: hidden;
  margin: 15px 0;
}

/* Progress fill */
.progress {
  width: 0%;
  height: 100%;
  background: linear-gradient(90deg, #00c6ff, #0072ff);
  transition: width 0.3s ease;
}

    #progressText { margin-top: 10px; font-size: 0.9rem; }
  </style>
</head>
<body>
  <div class="d-flex justify-content-end p-3">
    <button type="button" class="btn btn-outline-light rounded-pill" data-theme-toggle>Light mode</button>
  </div>
  <div class="container py-5">
    <?php if ($flash): ?>
      <div class="alert alert-<?= app_escape($flash['type']) ?>"><?= app_escape($flash['message']) ?></div>
    <?php endif; ?>
    <h2>📚 My Library</h2>
  <div class="game-row">
  <?php if ($result && $result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="game-card">
        <!-- Remove button -->
        <form method="POST" action="remove_from_library.php" style="position:absolute; top:8px; right:8px;">
   <input type="hidden" name="game_id" value="<?php echo $row['game_id']; ?>">
    <button type="submit" class="remove-button" title="Remove">&times;</button>
</form>



        <img src="<?php echo $row['image']; ?>" alt="<?php echo $row['title']; ?>">
        <div class="game-info">
          <p class="game-title"><?php echo $row['title']; ?></p>
        </div>

      <!-- Download button (inside your game loop) -->
<button class="game-btn" id="btn-<?php echo $row['id']; ?>" 
        onclick="startDownload('<?php echo $row['id']; ?>','<?php echo $row['title']; ?>')">
  Download
</button>

      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p>No games in your library yet.</p>
  <?php endif; ?>
</div>


  <script>
function startDownload(gameId, gameTitle) {
  const modal = document.getElementById("downloadModal");
  const title = document.getElementById("downloadGameTitle");
  const progress = document.querySelector(".progress");
  const progressText = document.getElementById("progressText");
  const button = document.getElementById("btn-" + gameId);

  title.innerText = "Downloading " + gameTitle + "...";
  progress.style.width = "0%";
  progressText.innerText = "0%";
  modal.style.display = "flex";

  let percent = 0;
  let interval = setInterval(() => {
    if (percent >= 100) {
      clearInterval(interval);
      title.innerText = gameTitle + " Installed ✔";
      progressText.innerText = "Completed";

      // Change Download → Play
      button.innerText = "▶ Play";
      button.classList.remove("game-btn");
      button.classList.add("play-btn");
      button.onclick = () => {
        alert("Launching " + gameTitle + " 🚀");
      };

      setTimeout(() => { modal.style.display = "none"; }, 1200);
    } else {
      percent += Math.floor(Math.random() * 7) + 3;
      if (percent > 100) percent = 100;
      progress.style.width = percent + "%";
      progressText.innerText = percent + "%";
    }
  }, 300);
}

// Close modal if clicked outside
window.onclick = function(e) {
  const modal = document.getElementById("downloadModal");
  if (e.target === modal) {
    modal.style.display = "none";
  }
}
</script>

<!-- Download Modal -->
<div id="downloadModal" class="modal">
  <div class="modal-content">
    <h3 id="downloadGameTitle"></h3>
    <div class="progress-bar">
      <div class="progress"></div>
    </div>
    <p id="progressText">0%</p>
  </div>
</div>

  <!-- Back to Store Button -->
<div style="margin: 20px 0; text-align: center;">
    <a href="index.php" 
       class="btn btn-primary" 
       style="padding: 10px 20px; border-radius: 25px; text-decoration: none; font-weight: bold;">
        ← Back to Store
    </a>
</div>

</body>
</html>

<?php $conn->close(); ?>
