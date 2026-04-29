<?php
$conn = new mysqli("localhost", "root", "", "game_store");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = isset($_GET['query']) ? $conn->real_escape_string($_GET['query']) : '';
$sql = "SELECT * FROM games WHERE title LIKE '%$query%'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0f0f0f, #1b1b1b);
            color: white;
            font-family: 'Segoe UI', sans-serif;
            overflow-x: hidden;
        }
        h2 {
            margin-bottom: 20px;
            font-weight: 600;
            animation: fadeInDown 1s ease;
        }

        /* Game Row */
        .game-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        /* Game Card */
        .game-card {
            background: rgba(42, 42, 42, 0.95);
            border-radius: 16px;
            overflow: hidden;
            width: 250px;
            color: white;
            flex-shrink: 0;
            box-shadow: 0 6px 16px rgba(0,0,0,0.6);
            transform: translateY(30px);
            opacity: 0;
            animation: fadeUp 0.6s forwards;
        }
        .game-card:nth-child(1) { animation-delay: 0.1s; }
        .game-card:nth-child(2) { animation-delay: 0.2s; }
        .game-card:nth-child(3) { animation-delay: 0.3s; }
        .game-card:nth-child(4) { animation-delay: 0.4s; }
        .game-card:nth-child(5) { animation-delay: 0.5s; }

        .game-card:hover {
            transform: translateY(-10px) scale(1.03);
            box-shadow: 0 10px 25px rgba(0,0,0,0.8);
        }

        .game-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            transition: transform 0.4s ease;
        }
        .game-card:hover img {
            transform: scale(1.1);
        }

        .game-info {
            padding: 15px;
        }
        .game-title {
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .game-desc {
            font-size: 0.9rem;
            color: #ccc;
            height: 40px;
            overflow: hidden;
        }

        /* Button */
        .game-btn {
            padding: 8px 14px;
            font-size: 0.85rem;
            font-weight: bold;
            border: none;
            border-radius: 20px;
            background: linear-gradient(90deg, #00cfff, #0088ff);
            color: black;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }
        .game-btn:hover {
            background: linear-gradient(90deg, #00e0ff, #0099ff);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 200, 255, 0.6);
            transform: scale(1.05);
        }

        /* Back button */
        .btn-light {
            transition: all 0.3s ease;
        }
        .btn-light:hover {
            background: #00cfff;
            color: black;
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(0,200,255,0.4);
        }

        /* Animations */
        @keyframes fadeUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes fadeInDown {
            from { transform: translateY(-30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <h2>Search Results for "<?php echo htmlspecialchars($query); ?>"</h2>
        <div class="game-row">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="game-card">
                        <!-- Clickable game card -->
                        <a href="view_game.php?id=<?php echo $row['id']; ?>" style="text-decoration: none; color: inherit; display:block;">
                            <img src="<?php echo $row['image']; ?>" alt="<?php echo $row['title']; ?>">
                            <div class="game-info">
                                <p class="game-title"><?php echo $row['title']; ?></p>
                                <p class="game-desc"><?php echo $row['description']; ?></p>
                            </div>
                        </a>

                        <!-- FREE/BUY button -->
                        <div class="px-3 pb-3">
                            <form action="add_to_library.php" method="POST">
                                <input type="hidden" name="game_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="game-btn">
                                    <?php echo (strtolower($row['Price']) === 'free') ? 'FREE' : 'BUY NOW'; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No games found matching your search.</p>
            <?php endif; ?>
        </div>

        <a href="index.php" class="btn btn-light mt-4">← Back to Store</a>
    </div>
</body>
</html>

<?php $conn->close(); ?>
