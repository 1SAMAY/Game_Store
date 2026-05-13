<?php
session_start();
require_once 'db.php';
require_admin();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $category = trim($_POST['category'] ?? '');

    $description = $_POST['description'] === 'Custom' && !empty($_POST['description_custom'])
        ? trim($_POST['description_custom'])
        : trim($_POST['description'] ?? '');

    $media_url = trim($_POST['media_url'] ?? '');

    $min_req = $_POST['min_requirement'] === 'Custom' && !empty($_POST['min_requirement_custom'])
        ? trim($_POST['min_requirement_custom'])
        : trim($_POST['min_requirement'] ?? '');

    $max_req = $_POST['max_requirement'] === 'Custom' && !empty($_POST['max_requirement_custom'])
        ? trim($_POST['max_requirement_custom'])
        : trim($_POST['max_requirement'] ?? '');

    $story = $_POST['story'] === 'Custom' && !empty($_POST['story_custom'])
        ? trim($_POST['story_custom'])
        : trim($_POST['story'] ?? '');

    $rating = isset($_POST['rating']) ? floatval($_POST['rating']) : 0;
    $imagePath = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif'])) {
            $rootDir = dirname(__DIR__);
            $imagesDir = $rootDir . '/images/';
            if (!is_dir($imagesDir)) mkdir($imagesDir, 0777, true);

            $fileName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($_FILES['image']['name']));
            $imagesPath = $imagesDir . $fileName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $imagesPath)) {
                $imagePath = 'images/' . $fileName;
            } else {
                $msg = 'Failed to move uploaded file into images/. Check folder permissions.';
            }
        } else {
            $msg = 'Invalid image type. Allowed: JPG, PNG, GIF.';
        }
    } else {
        $msg = 'No image uploaded or upload error.';
    }

    $sql = "INSERT INTO games 
        (title, price, category, description, image, media_url, min_requirement, max_requirement, story, rating) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die('SQL Prepare failed: ' . $conn->error);
    }
    $stmt->bind_param('sssssssssd', 
        $title, $price, $category, $description, 
        $imagePath, $media_url, $min_req, $max_req, $story, $rating
    );
    if ($stmt->execute()) {
        $msg = 'Game added successfully!';
    } else {
        $msg = 'Error: ' . $conn->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Game</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
    function toggleDescriptionCustom(sel) {
        document.getElementById('description_custom').style.display = (sel.value === 'Custom') ? 'block' : 'none';
    }
    function toggleStoryCustom(sel) {
        document.getElementById('story_custom').style.display = (sel.value === 'Custom') ? 'block' : 'none';
    }
    function toggleRequirementCustom(sel, id) {
        document.getElementById(id).style.display = (sel.value === 'Custom') ? 'block' : 'none';
    }
    </script>
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container py-5">
    <h2>Add New Game</h2>
    <?php if ($msg): ?>
        <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Price</label>
            <select name="price" class="form-control" required>
                <option value="Free">Free</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Category</label>
            <select name="category" class="form-control" required>
                <option value="Action">Action</option>
                <option value="Adventure">Adventure</option>
                <option value="RPG">RPG</option>
                <option value="Shooter">Shooter</option>
                <option value="Sports">Sports</option>
                <option value="Racing">Racing</option>
                <option value="Strategy">Strategy</option>
            </select>
        </div>
        <div class="col-md-12">
            <label class="form-label">Game Description</label>
            <select name="description" class="form-control" onchange="toggleDescriptionCustom(this)">
                <option value="">Select...</option>
                <option value="Fast-paced action gameplay with stunning graphics">Fast-paced action gameplay with stunning graphics</option>
                <option value="Immersive open-world with endless exploration">Immersive open-world with endless exploration</option>
                <option value="Multiplayer competitive battles with friends">Multiplayer competitive battles with friends</option>
                <option value="Story-rich single-player campaign">Story-rich single-player campaign</option>
                <option value="Casual fun gameplay suitable for all ages">Casual fun gameplay suitable for all ages</option>
                <option value="Custom">Custom</option>
            </select>
            <textarea id="description_custom" name="description_custom" class="form-control mt-2" style="display:none;" placeholder="Enter custom description"></textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">Image (Icon & Banner)</label>
            <input type="file" name="image" accept="image/*" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Video/Trailer Link</label>
            <input type="text" name="media_url" class="form-control">
        </div>
        <div class="col-md-6">
            <label class="form-label">Minimum Requirement</label>
            <select name="min_requirement" class="form-control" onchange="toggleRequirementCustom(this,'min_req_custom')" required>
                <option value="Intel i3, 4GB RAM, GTX 750">Intel i3, 4GB RAM, GTX 750</option>
                <option value="Intel i5, 8GB RAM, GTX 960">Intel i5, 8GB RAM, GTX 960</option>
                <option value="Intel i7, 8GB RAM, GTX 1060">Intel i7, 8GB RAM, GTX 1060</option>
                <option value="AMD Ryzen 3, 8GB RAM, RX 570">AMD Ryzen 3, 8GB RAM, RX 570</option>
                <option value="Custom">Custom</option>
            </select>
            <textarea id="min_req_custom" name="min_requirement_custom" class="form-control mt-2" style="display:none;" placeholder="OS:\nCPU:\nRAM:\nGPU:\nStorage:"></textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">Maximum Requirement</label>
            <select name="max_requirement" class="form-control" onchange="toggleRequirementCustom(this,'max_req_custom')" required>
                <option value="Intel i5, 8GB RAM, GTX 1060">Intel i5, 8GB RAM, GTX 1060</option>
                <option value="Intel i7, 16GB RAM, GTX 1660">Intel i7, 16GB RAM, GTX 1660</option>
                <option value="Intel i9, 16GB RAM, RTX 2060">Intel i9, 16GB RAM, RTX 2060</option>
                <option value="AMD Ryzen 5, 16GB RAM, RX 580">AMD Ryzen 5, 16GB RAM, RX 580</option>
                <option value="Custom">Custom</option>
            </select>
            <textarea id="max_req_custom" name="max_requirement_custom" class="form-control mt-2" style="display:none;" placeholder="OS:\nCPU:\nRAM:\nGPU:\nStorage:"></textarea>
        </div>
        <div class="col-md-12">
            <label class="form-label">Game Story</label>
            <select name="story" class="form-control" onchange="toggleStoryCustom(this)">
                <option value="">Select...</option>
                <option value="Epic Adventure with Hero Journey">Epic Adventure with Hero Journey</option>
                <option value="Open World Survival Story">Open World Survival Story</option>
                <option value="Sci-Fi Futuristic Narrative">Sci-Fi Futuristic Narrative</option>
                <option value="Fantasy World with Magic & Dragons">Fantasy World with Magic & Dragons</option>
                <option value="Custom">Custom</option>
            </select>
            <textarea id="story_custom" name="story_custom" class="form-control mt-2" style="display:none;" placeholder="Enter custom game story"></textarea>
        </div>
        <div class="col-md-12">
            <button class="btn btn-success">Add Game</button>
        </div>
    </form>
</div>
</body>
</html>
