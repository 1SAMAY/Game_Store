 
<?php
/*******************************************************
 * view_game.php
 * Epic-style Game Details (uses your provided SQL schema)
 *
 * Expected table: `games` with columns:
 * id, title, image, description, Category, Price,
 * media_url, min_requirement, max_requirement, story, rating
 *
 * - Full-page blurred background using the game's image
 * - Glassmorphism card, anime-shine, fade-in animations
 * - Media hero supports images, video files (mp4/webm/ogg) and YouTube links
 * - Thumbnails swap hero (click)
 * - GET button links to add_to_library.php (keeps your flow)
 *
 * Edit DB credentials below if needed.
 *******************************************************/

error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once "app_helpers.php";
require_once "db.php";

// ---------- DB config ----------
$dbConfig = app_db_config();

// ---------- helpers ----------
function safe($val) { return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8'); }

// Split lists in image/media fields (comma / semicolon / newline)
function explode_media_list($s) {
    if (!$s) return [];
    $parts = preg_split('/[\r\n,;]+/', $s);
    $out = [];
    foreach ($parts as $p) {
        $p = trim($p);
        if ($p !== '') $out[] = $p;
    }
    return $out;
}

// Parse requirements text like: "OS: Windows 10; CPU: Intel i5-4460; RAM: 8GB"
function parse_requirements($text) {
    if (!$text) return null;
    $pairs = preg_split('/\s*;\s*/', $text);
    $out = [];
    foreach ($pairs as $p) {
        $p = trim($p);
        if ($p === '') continue;
        $parts = explode(':', $p, 2);
        if (count($parts) === 2) {
            $k = trim($parts[0]);
            $v = trim($parts[1]);
            $out[$k] = $v;
        } else {
            $out[] = $p;
        }
    }
    return $out;
}

// Detect if URL is YouTube and return embed URL if possible
function youtube_embed_url($url) {
    if (!$url) return null;
    // youtube.com/watch?v=ID or youtu.be/ID
    if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
        // Try to extract ID
        if (preg_match('/(?:v=|\/v\/|youtu\.be\/|\/embed\/)([A-Za-z0-9_\-]{6,})/', $url, $m)) {
            $id = $m[1];
            return 'https://www.youtube.com/embed/' . rawurlencode($id) . '?rel=0';
        }
        return $url;
    }
    return null;
}

// Is direct video file?
function is_video_file($url) {
    $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
    return in_array($ext, ['mp4','webm','ogg']);
}

// ---------- read params ----------
$gameId    = isset($_GET['id']) ? (int)$_GET['id'] : null;
$gameTitle = isset($_GET['title']) ? urldecode($_GET['title']) : null;

// ---------- try connect to DB ----------
$pdo = null;
try {
    $pdo = new PDO(
        sprintf(
            "pgsql:host=%s;port=%d;dbname=%s;sslmode=%s",
            $dbConfig['host'],
            $dbConfig['port'],
            $dbConfig['database'],
            $dbConfig['sslmode']
        ),
        $dbConfig['username'],
        $dbConfig['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (Throwable $e) {
    $pdo = null;
}

// ---------- fallback sample (only used if DB missing or no result) ----------
$gamesFallback = [
  "Far Cry 6" => [
    "image"=>"images/Far Cry 6.jpg",
    "Price"=>"Free",
    "description"=>"Fight against a modern-day dictatorship in the tropical island of Yara.",
    "story"=>"Lead a modern-day guerrilla revolution to liberate the island nation of Yara.",
    "media_url"=>"images/Far Cry 6.jpg",
    "min_requirement"=>"OS: Windows 10; CPU: Intel i5-4460; GPU: GTX 960; RAM: 8 GB; Storage: 80 GB",
    "max_requirement"=>"OS: Windows 10; CPU: Intel i7-7700; GPU: GTX 1080; RAM: 16 GB; Storage: 80 GB",
    "Category"=>"Shooter, Open World",
    "rating"=>4.1
  ]
];

// ---------- fetch game ----------
$game = null;
if ($pdo) {
    try {
        if ($gameId) {
            $stmt = $pdo->prepare("SELECT * FROM games WHERE id = ? LIMIT 1");
            $stmt->execute([$gameId]);
        } elseif ($gameTitle) {
            $stmt = $pdo->prepare("SELECT * FROM games WHERE title = ? LIMIT 1");
            $stmt->execute([$gameTitle]);
        }
        if (isset($stmt)) {
            $row = $stmt->fetch();
            if ($row) {
                // normalize keys to lowercase to handle Category/Price case
                $r = array_change_key_case($row, CASE_LOWER);

                // parse image (may contain multiple entries)
                $images = explode_media_list($r['image'] ?? ($row['image'] ?? ''));
                $mediaUrls = explode_media_list($r['media_url'] ?? ($row['media_url'] ?? ''));

                // Build screenshots: media images then any leftover images
                $screens = [];
                $trailer = null;
                foreach ($mediaUrls as $m) {
                    if (youtube_embed_url($m)) {
                        if (!$trailer) $trailer = youtube_embed_url($m);
                    } elseif (is_video_file($m)) {
                        if (!$trailer) $trailer = $m;
                    } else {
                        $screens[] = $m;
                    }
                }
                // add images field items as screenshots if none
                foreach ($images as $img) {
                    // If this looks like a video link, treat accordingly
                    if (youtube_embed_url($img) && !$trailer) $trailer = youtube_embed_url($img);
                    elseif (is_video_file($img) && !$trailer) $trailer = $img;
                    else $screens[] = $img;
                }

                $cover = $images[0] ?? $screens[0] ?? ($r['image'] ?? '');
                $banner = $cover;

                $game = [
                    'id' => isset($r['id']) ? (int)$r['id'] : null,
                    'title' => $r['title'] ?? ($row['title'] ?? 'Untitled'),
                    'price' => $r['price'] ?? ($r['Price'] ?? ($row['Price'] ?? 'Free')),
                    'cover_image' => $cover,
                    'banner_image' => $banner,
                    'short_description' => $r['description'] ?? ($row['description'] ?? ''),
                    'long_description' => $r['story'] ?? ($r['description'] ?? ($row['story'] ?? $row['description'] ?? '')),
                    'min_requirement' => $r['min_requirement'] ?? ($row['min_requirement'] ?? null),
                    'max_requirement' => $r['max_requirement'] ?? ($row['max_requirement'] ?? null),
                    'requirements_parsed' => [
                        'minimum' => parse_requirements($r['min_requirement'] ?? ($row['min_requirement'] ?? '')),
                        'recommended' => parse_requirements($r['max_requirement'] ?? ($row['max_requirement'] ?? ''))
                    ],
                    'screenshots' => array_values(array_unique($screens)),
                    'trailer' => $trailer,
                    'category' => $r['category'] ?? ($row['Category'] ?? ''),
                    'rating' => isset($r['rating']) ? (float)$r['rating'] : (isset($row['rating']) ? (float)$row['rating'] : null)
                ];
            }
        }
    } catch (Throwable $e) {
        // DB read error -> fall back
        $game = null;
    }
}

// fallback if no DB result
if (!$game) {
    $key = $gameTitle ?: array_key_first($gamesFallback);
    $fb = $gamesFallback[$key] ?? null;
    if ($fb) {
        $screens = explode_media_list($fb['media_url'] ?? $fb['image'] ?? '');
        $game = [
            'id'=>null,
            'title'=>$key,
            'price'=>$fb['Price'] ?? $fb['price'] ?? 'Free',
            'cover_image'=>$fb['image'] ?? ($screens[0] ?? ''),
            'banner_image'=>$fb['image'] ?? ($screens[0] ?? ''),
            'short_description'=>$fb['description'] ?? '',
            'long_description'=>$fb['story'] ?? ($fb['description'] ?? ''),
            'min_requirement'=>$fb['min_requirement'] ?? null,
            'max_requirement'=>$fb['max_requirement'] ?? null,
            'requirements_parsed'=>[
                'minimum'=>parse_requirements($fb['min_requirement'] ?? ''),
                'recommended'=>parse_requirements($fb['max_requirement'] ?? '')
            ],
            'screenshots'=>$screens,
            'trailer'=>null,
            'category'=>$fb['Category'] ?? $fb['category'] ?? '',
            'rating'=>isset($fb['rating']) ? (float)$fb['rating'] : null
        ];
    }
}

$flash = app_take_flash();
$currentUserId = app_current_user_id();
if ($game && $currentUserId && !empty($game['id'])) {
    app_track_recent_view($conn, $currentUserId, (int) $game['id']);
}

$reviews = [];
$avgRating = null;
$myReview = null;
$relatedGames = [];
$userCollections = [];

if ($game && !empty($game['id'])) {
    $gameIdForQueries = (int) $game['id'];

    $reviewStmt = $conn->prepare(
        "SELECT r.rating, r.review_text, r.updated_at, COALESCE(u.display_name, u.username) AS reviewer
         FROM reviews r
         JOIN users u ON u.id = r.user_id
         WHERE r.game_id = ?
         ORDER BY r.updated_at DESC
         LIMIT 12"
    );
    if ($reviewStmt) {
        $reviewStmt->bind_param('i', $gameIdForQueries);
        $reviewStmt->execute();
        $reviewResult = $reviewStmt->get_result();
        while ($reviewRow = $reviewResult->fetch_assoc()) {
            $reviews[] = $reviewRow;
        }
        $reviewStmt->close();
    }

    $ratingStmt = $conn->prepare("SELECT AVG(rating) AS avg_rating FROM reviews WHERE game_id = ?");
    if ($ratingStmt) {
        $ratingStmt->bind_param('i', $gameIdForQueries);
        $ratingStmt->execute();
        $ratingResult = $ratingStmt->get_result();
        $ratingRow = $ratingResult ? $ratingResult->fetch_assoc() : null;
        $avgRating = $ratingRow && $ratingRow['avg_rating'] !== null ? (float) $ratingRow['avg_rating'] : null;
        $ratingStmt->close();
    }

    if ($currentUserId) {
        $mineStmt = $conn->prepare(
            "SELECT rating, review_text FROM reviews WHERE game_id = ? AND user_id = ? LIMIT 1"
        );
        if ($mineStmt) {
            $mineStmt->bind_param('ii', $gameIdForQueries, $currentUserId);
            $mineStmt->execute();
            $mineResult = $mineStmt->get_result();
            $myReview = $mineResult ? $mineResult->fetch_assoc() : null;
            $mineStmt->close();
        }
    }

    $relatedStmt = $conn->prepare(
        "SELECT id, title, image, price
         FROM games
         WHERE id <> ? AND (category LIKE ? OR title LIKE ?)
         ORDER BY rating DESC, id DESC
         LIMIT 4"
    );
    if ($relatedStmt) {
        $categoryLike = '%' . ($game['category'] ?: '') . '%';
        $titleLike = '%' . $game['title'] . '%';
        $relatedStmt->bind_param('iss', $gameIdForQueries, $categoryLike, $titleLike);
        $relatedStmt->execute();
        $relatedResult = $relatedStmt->get_result();
        while ($related = $relatedResult->fetch_assoc()) {
            $relatedGames[] = $related;
        }
        $relatedStmt->close();
    }

    if ($currentUserId) {
        $collectionsStmt = $conn->prepare("SELECT id, name FROM collections WHERE user_id = ? ORDER BY created_at DESC");
        if ($collectionsStmt) {
            $collectionsStmt->bind_param('i', $currentUserId);
            $collectionsStmt->execute();
            $collectionsResult = $collectionsStmt->get_result();
            while ($collectionRow = $collectionsResult->fetch_assoc()) {
                $userCollections[] = $collectionRow;
            }
            $collectionsStmt->close();
        }
    }
}

$titleSafe = $game ? safe($game['title']) : 'Game Not Found';
$bgImage = $game['banner_image'] ?: $game['cover_image'] ?: '';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= $titleSafe ?> — Game Details</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    :root{
      --bg:#080808;
      --glass: rgba(255,255,255,0.03);
      --glass-2: rgba(255,255,255,0.03);
      --accent:#00e0d0;
      --accent-2:#00b4e0;
      --muted:#bfc7d0;
      --card-border: rgba(255,255,255,0.06);
    }
    *{box-sizing:border-box}
    html,body{height:100%; margin:0; font-family: Inter, "Segoe UI", Roboto, Arial, sans-serif; color:#e9eef6; background:var(--bg);}

    /* Full-page background using game banner image */
    body::before{
      content:'';
      position:fixed; inset:0;
      background-image: url('<?= safe($bgImage) ?>');
      background-size:cover; background-position:center;
      filter: blur(18px) saturate(0.86) brightness(0.45);
      transform: scale(1.02);
      z-index: -2;
      transition: background-image .6s ease;
    }
    /* subtle vignette */
    body::after{
      content:'';
      position:fixed; inset:0; z-index:-1;
      background: linear-gradient(to bottom, rgba(0,0,0,0.25), rgba(3,6,12,0.75));
      pointer-events:none;
    }

    /* Container */
    .container {
      max-width:1200px; margin: 44px auto 80px; padding: 28px;
      animation: fadeIn .6s ease both;
    }

    /* Glass card (main area) */
.glass {
  background: rgba(255,255,255,0.08); /* light transparent glass */
  border: 1px solid var(--card-border);
  border-radius: 14px;
  backdrop-filter: blur(12px) saturate(1.2);
  -webkit-backdrop-filter: blur(12px) saturate(1.2);
  overflow: hidden;
}

    /* Layout: left poster + right content */
    .layout {
      display:grid; grid-template-columns: 420px 1fr; gap: 28px; align-items:start;
    }
    @media (max-width: 980px) {
      .layout { grid-template-columns: 1fr; }
      .sticky { position: static !important; }
    }


/* 🔥 Glow effect on buttons */
button, .cta {
  transition: all 0.3s ease;
}
button:hover, .cta:hover {
  box-shadow: 0 0 12px rgba(255,255,255,0.4);
  transform: scale(1.05);
}

/* ✨ Card hover effects */
.card, .glass, .sticky, .media-hero {
  transition: transform 0.4s ease, box-shadow 0.4s ease;
}
.card:hover, .glass:hover, .sticky:hover, .media-hero:hover {
  transform: translateY(-6px) scale(1.02);
  box-shadow: 0 8px 25px rgba(0,0,0,0.35);
}

    

    /* Poster card */
    .poster-card { padding: 18px; }
    .poster-wrap{ position:relative; overflow:hidden; border-radius:12px; border:1px solid var(--card-border); }
    .poster-wrap img{ width:100%; height:auto; display:block; transform-origin:center center; transition: transform .6s cubic-bezier(.2,.9,.2,1); }
    .poster-wrap:hover img{ transform: scale(1.04); }

    /* anime-shine CTA on poster */
    .poster-wrap::after{
      content:''; position:absolute; left:-60%; top:-80%;
      width:40%; height:240%;
      background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.18) 50%, rgba(255,255,255,0) 100%);
      transform: rotate(22deg);
      transition: all .9s ease;
      opacity:0.9;
    }
    .poster-wrap:hover::after{ left:120%; transition: all .9s ease; }

    /* Sticky purchase block */
.sticky {
  background: rgba(255,255,255,0.06); /* frosted glass instead of black */
  border-radius: 12px;
  border: 1px solid var(--card-border);
  backdrop-filter: blur(10px) saturate(1.2);
  -webkit-backdrop-filter: blur(10px) saturate(1.2);
}
    .title-row { display:flex; gap:18px; align-items:center; flex-wrap:wrap; }
    .game-title { font-size:34px; font-weight:800; margin:0; letter-spacing:-0.4px; color:#f7fbff; }
    .subtitle { color:var(--muted); font-size:14px; }

    .price { color:var(--accent); font-weight:800; font-size:20px; margin-top:6px; display:block; }

    /* CTA button with glow & anime-shine */
    .cta {
      display:inline-block; width:100%;
      padding:14px 18px; border-radius:10px; border:none; text-align:center;
      background: linear-gradient(90deg, var(--accent), var(--accent-2));
      color:#012; font-weight:800; font-size:18px; text-decoration:none;
      box-shadow: 0 8px 30px rgba(0,200,200,0.08), 0 2px 8px rgba(0,0,0,0.35);
      position:relative; overflow:hidden; transition: transform .15s ease, box-shadow .2s ease;
    }
    .cta:hover { transform: translateY(-4px); box-shadow: 0 18px 60px rgba(0,180,200,0.14); }
    .cta::after{
      content:''; position:absolute; left:-80%; top:-60%; width:30%; height:260%;
      background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.18) 50%, rgba(255,255,255,0) 100%);
      transform: rotate(25deg);
      transition: left .8s cubic-bezier(.2,.9,.2,1);
      opacity:.95;
    }
    .cta:hover::after{ left:120%; }

    .sub-links { display:flex; gap:10px; margin-top:12px; }
    .sub-links a { color:var(--muted); font-size:14px; text-decoration:none; padding:8px 10px; border-radius:8px; border:1px solid transparent; background:rgba(255,255,255,0.02); }
    .sub-links a:hover { color:#fff; border-color:var(--card-border); }

   /* Media hero */
.media-hero {
  border-radius: 10px;
  overflow: hidden;
  border: 1px solid var(--card-border);
  background: rgba(255,255,255,0.05); /* frosted glass background */
  backdrop-filter: blur(10px) saturate(1.2);
  -webkit-backdrop-filter: blur(10px) saturate(1.2);
  height: 360px;
  display: flex;
  align-items: center;
  justify-content: center;
}


    .thumbs { display:flex; gap:10px; margin-top:12px; overflow:auto; padding-bottom:6px; }
    .thumb { flex:0 0 120px; height:70px; border-radius:8px; overflow:hidden; border:1px solid var(--card-border); cursor:pointer; }
    .thumb img, .thumb video {
  width:100%;
  height:100%;
  object-fit:cover;
  display:block;
  transition: transform .12s ease;
}
.thumb:hover img, .thumb:hover video { transform: scale(1.06); }


    /* Sections */
    .section { margin-top:18px; }
    .h2 { font-size:16px; font-weight:700; margin:0 0 10px; color:#eaf6ff; }
    .text-muted { color:var(--muted); line-height:1.6; }

    .req-grid { display:grid; grid-template-columns: 1fr 1fr; gap:12px; }
    @media(max-width:720px){ .req-grid{ grid-template-columns:1fr; } }
    .req-card { background: linear-gradient(180deg, rgba(255,255,255,0.01), rgba(255,255,255,0.00)); border-radius:10px; padding:12px; border:1px solid var(--card-border); }

    /* small UI */
    .tags { display:flex; gap:8px; flex-wrap:wrap; margin-top:10px; }
    .tag { padding:6px 10px; border-radius:999px; background: rgba(255,255,255,0.02); border:1px solid var(--card-border); color:#e2f9f5; font-size:13px; }

    .muted { color:var(--muted); }
    .small { font-size:13px; color:var(--muted); }

    /* Animations */
    @keyframes fadeIn { from { opacity:0; transform: translateY(8px); } to { opacity:1; transform: none; } }
  </style>
  <script src="theme.js" defer></script>
</head>
<body>
  <div class="container">
    <div class="d-flex justify-content-end mb-3">
      <button type="button" class="btn btn-outline-light rounded-pill" data-theme-toggle>Light mode</button>
    </div>
    <?php if ($flash): ?>
      <div class="glass" style="padding:12px 16px; margin-bottom:16px; border-color: rgba(0, 224, 208, 0.18);">
        <?= safe($flash['message']) ?>
      </div>
    <?php endif; ?>
    <?php if ($game): ?>
      <div class="glass" style="padding:20px;">
        <div class="layout">
          <!-- LEFT: Poster & sticky purchase -->
          <div>
            <div class="poster-card">
              <div class="poster-wrap">
                <img id="coverImg" src="<?= safe($game['cover_image']) ?>" alt="<?= $titleSafe ?>">
              </div>

              <div class="sticky" style="margin-top:14px;">
                <div class="small muted">Base Game</div>
                <div class="price"><?= safe($game['price'] ?? 'Free') ?></div>

                <?php
                  $libLink = $game['id'] ? "add_to_library.php?game_id=".$game['id'] : "add_to_library.php?title=".urlencode($game['title']);
                ?>
<form method="POST" action="add_to_library.php" style="display:inline;">
    <input type="hidden" name="game_id" value="<?= (int)$game['id'] ?>">
    <button type="submit" class="cta">
        <?= (strtolower($game['price'] ?? 'free') === 'free' ? 'FREE' : 'BUY NOW') ?>
    </button>
</form>

                <?php if (app_is_logged_in() && $userCollections): ?>
                  <form method="POST" action="add_to_collection.php" class="mt-3">
                    <input type="hidden" name="game_id" value="<?= (int) $game['id'] ?>">
                    <label class="small muted mb-2">Save to a collection</label>
                    <div class="d-flex gap-2">
                      <select name="collection_id" class="form-select">
                        <?php foreach ($userCollections as $collection): ?>
                          <option value="<?= (int) $collection['id'] ?>"><?= safe($collection['name']) ?></option>
                        <?php endforeach; ?>
                      </select>
                      <button type="submit" class="btn btn-outline-light">Add</button>
                    </div>
                  </form>
                <?php elseif (app_is_logged_in()): ?>
                  <div class="small muted mt-3">Create a collection first to save games into it.</div>
                <?php endif; ?>

                <div class="sub-links">
                  <a href="library.php">Library</a>
                  <a href="collections.php">Collections</a>
                  <a href="index.php">Back to Store</a>
                </div>

                <div style="margin-top:12px;">
                  <div class="small muted">Category</div>
                  <div class="tags">
                    <?php
                      $cats = preg_split('/[,\/]+/', $game['category'] ?? '');
                      foreach ($cats as $c) {
                        $c = trim($c);
                        if ($c === '') continue;
                        echo '<div class="tag">'.safe($c).'</div>';
                      }
                    ?>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- RIGHT: main content -->
          <div style="padding:18px;">
            <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
              <div>
                <div class="title-row">
                  <h1 class="game-title"><?= $titleSafe ?></h1>
                </div>
                <div class="small muted" style="margin-top:6px;"><?= nl2br(safe($game['short_description'] ?? '')) ?></div>
              </div>

              <?php if (!empty($game['rating'])): ?>
                <div style="text-align:right;">
                  <div style="font-weight:800; color:#ffd66f;"><?= safe(number_format($game['rating'],1)) ?> ★</div>
                  <div class="small muted">User Rating</div>
                </div>
              <?php endif; ?>
            </div>

            <!-- Media hero -->
            <div class="section">
              <div class="media-hero glass" id="mediaHero">
                <?php
                  // Determine hero: trailer (embed/vid) else first screenshot else cover
                  $heroPrinted = false;
                  if (!empty($game['trailer'])) {
                    // trailer may be embed URL or direct video
                    $tr = $game['trailer'];
                    if (strpos($tr,'youtube.com') !== false || strpos($tr,'youtu.be') !== false) {
                      $embed = $tr;
                      echo '<iframe src="'.safe($embed).'" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
                      $heroPrinted = true;
                    } elseif (is_file($tr) || is_video_file($tr)) {
                      // use video tag
                      echo '<video id="heroVideo" src="'.safe($tr).'" controls playsinline style="width:100%;height:100%;object-fit:cover;"></video>';
                      $heroPrinted = true;
                    } else {
                      // fallback: show image link or first screenshot
                    }
                  }
                  if (!$heroPrinted) {
                    $heroImg = $game['screenshots'][0] ?? $game['cover_image'] ?? '';
                    echo '<img id="heroImg" src="'.safe($heroImg).'" alt="Media">';
                  }
                ?>
              </div>

             <?php
// Build combined media list (trailer first so it appears as a thumb), then screenshots
$allMedia = [];
if (!empty($game['trailer'])) $allMedia[] = $game['trailer'];
if (!empty($game['screenshots'])) {
    foreach ($game['screenshots'] as $m) $allMedia[] = $m;
}
$allMedia = array_values(array_unique($allMedia));
?>

<?php if (!empty($allMedia)): ?>
  <div class="thumbs" id="thumbs">
    <?php foreach ($allMedia as $m):
        $isYT = (strpos($m, 'youtube.com') !== false || strpos($m, 'youtu.be') !== false);
        $isVid = preg_match('/\.(mp4|webm|ogg)(\?.*)?$/i', $m);
    ?>
      <div class="thumb" data-src="<?= safe($m) ?>" data-type="<?= $isYT ? 'youtube' : ($isVid ? 'video' : 'image') ?>">
        <?php if ($isYT): 
            // get youtube id for thumbnail fallback
            $ytid = '';
            if (preg_match('/(?:v=|\/v\/|youtu\.be\/|\/embed\/)([A-Za-z0-9_\-]{6,})/', $m, $m2)) $ytid = $m2[1];
            $ytThumb = $ytid ? "https://img.youtube.com/vi/{$ytid}/hqdefault.jpg" : safe($game['cover_image']);
        ?>
            <img src="<?= $ytThumb ?>" alt="youtube">
        <?php elseif ($isVid): ?>
            <!-- use a small muted video as thumbnail preview -->
            <video src="<?= safe($m) ?>" muted playsinline preload="metadata"></video>
        <?php else: ?>
            <img src="<?= safe($m) ?>" alt="thumb">
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

            </div>

            <!-- About / Story -->
            <div class="section">
              <div class="h2">About</div>
              <div class="text-muted"><?= nl2br(safe($game['long_description'] ?? '')) ?></div>
            </div>

            <!-- Requirements -->
            <div class="section">
              <div class="h2">System Requirements</div>
              <div class="req-grid">
                <div class="req-card">
                  <div style="font-weight:700;margin-bottom:8px;">Minimum</div>
                  <?php $min = $game['requirements_parsed']['minimum'] ?? null; ?>
                  <?php if ($min && is_array($min) && count($min)>0): ?>
                    <table style="width:100%; border-collapse:collapse;">
                      <?php foreach ($min as $k=>$v): ?>
                        <tr>
                          <td style="width:120px;color:var(--muted);padding:6px 0;"><?= safe($k) ?></td>
                          <td style="padding:6px 0;"><?= safe($v) ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </table>
                  <?php else: ?>
                    <div class="muted"><?= nl2br(safe($game['min_requirement'] ?? 'No minimum requirements provided.')) ?></div>
                  <?php endif; ?>
                </div>

                <div class="req-card">
                  <div style="font-weight:700;margin-bottom:8px;">Recommended</div>
                  <?php $rec = $game['requirements_parsed']['recommended'] ?? null; ?>
                  <?php if ($rec && is_array($rec) && count($rec)>0): ?>
                    <table style="width:100%; border-collapse:collapse;">
                      <?php foreach ($rec as $k=>$v): ?>
                        <tr>
                          <td style="width:120px;color:var(--muted);padding:6px 0;"><?= safe($k) ?></td>
                          <td style="padding:6px 0;"><?= safe($v) ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </table>
                  <?php else: ?>
                    <div class="muted"><?= nl2br(safe($game['max_requirement'] ?? 'No recommended requirements provided.')) ?></div>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <div class="section">
              <div class="h2">Community Reviews</div>
              <div class="req-card" style="margin-bottom:14px;">
                <div class="small muted">Average rating</div>
                <div style="font-size:1.4rem; font-weight:800;">
                  <?= $avgRating !== null ? safe(number_format($avgRating, 1)) . ' ★' : 'No ratings yet' ?>
                </div>
              </div>

              <?php if (app_is_logged_in()): ?>
                <form method="POST" action="submit_review.php" class="req-card" style="margin-bottom:16px;">
                  <input type="hidden" name="game_id" value="<?= (int) $game['id'] ?>">
                  <div class="row g-3">
                    <div class="col-md-4">
                      <label class="form-label small muted">Your rating</label>
                      <select name="rating" class="form-select" required>
                        <option value="">Choose rating</option>
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                          <option value="<?= $i ?>" <?= ($myReview && (int)($myReview['rating'] ?? 0) === $i) ? 'selected' : '' ?>>
                            <?= $i ?> Star<?= $i > 1 ? 's' : '' ?>
                          </option>
                        <?php endfor; ?>
                      </select>
                    </div>
                    <div class="col-md-8">
                      <label class="form-label small muted">Write a review</label>
                      <textarea name="review_text" class="form-control" rows="3" placeholder="Share what you liked or disliked..." required><?= safe($myReview['review_text'] ?? '') ?></textarea>
                    </div>
                  </div>
                  <div class="mt-3">
                    <button type="submit" class="cta" style="max-width: 220px;">Save Review</button>
                  </div>
                </form>
              <?php else: ?>
                <div class="req-card" style="margin-bottom:16px;">
                  <div class="muted">Log in to leave a review and rating.</div>
                  <a href="login.php" class="cta" style="display:inline-block; width:auto; margin-top:12px; padding:10px 16px;">Login</a>
                </div>
              <?php endif; ?>

              <?php if ($reviews): ?>
                <div class="d-grid gap-3">
                  <?php foreach ($reviews as $review): ?>
                    <div class="req-card">
                      <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                          <div style="font-weight:700;"><?= safe($review['reviewer']) ?></div>
                          <div class="small muted"><?= safe($review['updated_at']) ?></div>
                        </div>
                        <div style="font-weight:800; color:#ffd66f; white-space:nowrap;"><?= (int) $review['rating'] ?> ★</div>
                      </div>
                      <p class="text-muted mb-0 mt-2"><?= nl2br(safe($review['review_text'])) ?></p>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div class="req-card">No reviews yet. Be the first to share your thoughts.</div>
              <?php endif; ?>
            </div>

            <?php if ($relatedGames): ?>
              <div class="section">
                <div class="h2">Similar Games</div>
                <div class="game-row">
                  <?php foreach ($relatedGames as $related): ?>
                    <div class="game-card" style="width: 100%;">
                      <a href="view_game.php?id=<?= (int) $related['id'] ?>">
                        <img src="<?= safe($related['image']) ?>" alt="<?= safe($related['title']) ?>" class="game-card-image">
                      </a>
                      <div class="game-card-body">
                        <p class="card-kicker"><?= safe($related['price'] ?? 'Free') ?></p>
                        <p class="card-title"><?= safe($related['title']) ?></p>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>

            <div style="height:18px;"></div>
            <a class="small muted" href="index.php">← Back to Store</a>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div class="glass" style="padding:24px;">
        <h1>Game Not Found</h1>
        <p class="muted">No game found for that id/title. Check URL or use store listing.</p>
        <a class="small muted" href="index.php">← Back to Store</a>
      </div>
    <?php endif; ?>
  </div>

  <script>
  (function(){
    const thumbs = document.getElementById('thumbs');
    if (!thumbs) return;
    const mediaHero = document.getElementById('mediaHero');

    // Detect media type
    function isVideoFile(url){
      const ext = url.split('.').pop().toLowerCase();
      return ['mp4','webm','ogg'].includes(ext);
    }

    function isYouTubeLink(url){
      return url.includes('youtube.com') || url.includes('youtu.be');
    }

    function getYouTubeEmbed(url){
      let id = null;
      const ytRegex = /(?:v=|\/v\/|youtu\.be\/|\/embed\/)([A-Za-z0-9_\-]{6,})/;
      const match = url.match(ytRegex);
      if(match) id = match[1];
      if(id) return 'https://www.youtube.com/embed/' + encodeURIComponent(id) + '?rel=0';
      return url;
    }

    thumbs.addEventListener('click', function(e){
      const t = e.target.closest('.thumb');
      if(!t) return;
      const src = t.getAttribute('data-src');
      if(!src) return;

      mediaHero.innerHTML = '';

      if(isYouTubeLink(src)){
        const iframe = document.createElement('iframe');
        iframe.src = getYouTubeEmbed(src);
        iframe.frameBorder = 0;
        iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
        iframe.allowFullscreen = true;
        iframe.style.width = '100%';
        iframe.style.height = '100%';
        mediaHero.appendChild(iframe);
      }
      else if(isVideoFile(src) || src.endsWith('.mp4') || src.endsWith('.webm') || src.endsWith('.ogg')){
        const video = document.createElement('video');
        video.src = src;
        video.controls = true;
        video.playsInline = true;
        video.muted = true; // optional: autoplay safety
        video.style.width = '100%';
        video.style.height = '100%';
        video.style.objectFit = 'cover';
        mediaHero.appendChild(video);
      }
      else {
        const img = document.createElement('img');
        img.src = src;
        img.alt = 'screenshot';
        img.style.width = '100%';
        img.style.height = '100%';
        img.style.objectFit = 'cover';
        mediaHero.appendChild(img);
      }
    });
  })();

  // Ensure hero video (if present) is muted
  (function(){
    const v = document.querySelector('#mediaHero video');
    if(v) v.muted = true;
  })();
</script>

</body>
</html>
