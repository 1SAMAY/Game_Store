<?php
session_start();
require_once "db.php";
require_admin();

// Total stats
$users     = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$games     = $conn->query("SELECT COUNT(*) AS total FROM games")->fetch_assoc()['total'];
$libraries = $conn->query("SELECT COUNT(DISTINCT game_id) AS total FROM library")->fetch_assoc()['total'];
$reviews   = $conn->query("SELECT COUNT(*) AS total FROM reviews")->fetch_assoc()['total'];
$collections = $conn->query("SELECT COUNT(*) AS total FROM collections")->fetch_assoc()['total'];

// Most played game
$mostSql = "SELECT g.title, COUNT(l.id) as count 
            FROM library l 
            JOIN games g ON l.game_id = g.id 
            GROUP BY l.game_id 
            ORDER BY count DESC LIMIT 1";
$mostRow   = $conn->query($mostSql)->fetch_assoc();
$mostGame  = $mostRow ? $mostRow['title'] : 'N/A';
$mostCount = $mostRow ? $mostRow['count'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #121212; color: #fff; }
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
            position: relative;
            overflow: hidden;
            background: #222;
            transition: background 0.2s ease-out;
        }
        /* Removed CSS shine animation in favor of JS dynamic shine */
        .card h2 { font-weight: bold; }
        .card-body { text-align: center; padding: 2rem; position: relative; z-index: 1; }
        .btn {
            border-radius: 50px;
            padding: 0.6rem 1.5rem;
            position: relative;
            overflow: hidden;
            color: #fff;
            background: #28a745;
            border: none;
            transition: box-shadow 0.3s ease;
            z-index: 0;
        }
        .btn::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg,
                rgba(255,255,255,0.15),
                rgba(255,255,255,0.4),
                rgba(255,255,255,0.15));
            filter: blur(10px);
            opacity: 0.8;
            z-index: -1;
            pointer-events: none;
        }
        .btn:hover {
            box-shadow: 0 0 15px 5px rgba(40,167,69,0.75);
            color: #fff;
        }
        /* Sparkle effect for buttons */
        .sparkle {
            position: absolute;
            width: 6px;
            height: 6px;
            background: white;
            filter: drop-shadow(0 0 2px white);
            border-radius: 50%;
            pointer-events: none;
            animation: sparkleAnim 1s linear infinite;
            opacity: 0;
        }
        @keyframes sparkleAnim {
            0% { transform: scale(0) rotate(0deg); opacity: 1; }
            50% { opacity: 1; }
            100% { transform: scale(1.5) rotate(180deg); opacity: 0; }
        }
    </style>
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container py-5">
    <h2 class="mb-4 text-center">Admin Dashboard</h2>
    <div class="row g-4 text-center">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Total Users</h5>
                    <h2><?= $users ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Total Games</h5>
                    <h2><?= $games ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5>Games in Libraries</h5>
                    <h2><?= $libraries ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5>Most Played Game</h5>
                    <h2><?= htmlspecialchars($mostGame) ?></h2>
                    <p class="mb-0"><?= $mostCount ?> times</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4 text-center mt-2">
        <div class="col-md-6">
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <h5>Total Reviews</h5>
                    <h2><?= (int) $reviews ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <h5>Collections Created</h5>
                    <h2><?= (int) $collections ?></h2>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-5 text-center">
        <a href="add_game.php" class="btn btn-success me-2">➕ Add New Game</a>
        <a href="manage_users.php" class="btn btn-primary me-2">👥 Manage Users</a>
        <a href="stats.php" class="btn btn-info">📊 View Stats</a>
    </div>
</div>

<script>
    // Dynamic shine on cards based on mouse position
    document.querySelectorAll('.card').forEach(card => {
        card.addEventListener('mousemove', e => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            // radial gradient shine at cursor
            card.style.background = `radial-gradient(circle at ${x}px ${y}px, rgba(255,255,255,0.3), transparent 80%), #222`;
        });
        card.addEventListener('mouseleave', () => {
            card.style.background = '#222';
        });
    });

    // Sparkle effect on buttons on hover
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('mouseenter', () => {
            for(let i = 0; i < 5; i++) {
                const sparkle = document.createElement('div');
                sparkle.classList.add('sparkle');
                sparkle.style.left = Math.random() * btn.offsetWidth + 'px';
                sparkle.style.top = Math.random() * btn.offsetHeight + 'px';
                btn.appendChild(sparkle);
                setTimeout(() => sparkle.remove(), 1000);
            }
        });
    });
</script>
</body>
</html>
