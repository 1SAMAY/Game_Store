<?php
require_once __DIR__ . '/config.php';

$db = app_db_config();
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
        <p class="mb-0 text-secondary">This project now uses Supabase/Postgres instead of the old MySQL bootstrap.</p>
      </div>
      <span class="badge bg-info text-dark rounded-pill px-3 py-2"><?= htmlspecialchars($db['driver']) ?></span>
    </div>

    <div class="alert alert-info">
      Use <code>supabase_schema.sql</code> to create the database schema in the Supabase SQL editor.
    </div>

    <div class="row g-4">
      <div class="col-md-6">
        <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.04);">
          <h5 class="mb-3">Connection settings</h5>
          <ul class="mb-0">
            <li><code>DB_DRIVER=pgsql</code></li>
            <li><code>DB_HOST</code> set to your Supabase host</li>
            <li><code>DB_PORT=5432</code></li>
            <li><code>DB_NAME</code> set to your Supabase database</li>
          </ul>
        </div>
      </div>
      <div class="col-md-6">
        <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.04);">
          <h5 class="mb-3">Next steps</h5>
          <ol class="mb-0">
            <li>Run the SQL file in Supabase.</li>
            <li>Deploy the PHP app on Render.</li>
            <li>Set your Render environment variables.</li>
          </ol>
        </div>
      </div>
    </div>

    <div class="mt-4">
      <a href="index.php" class="btn btn-info me-2">Open Store</a>
      <a href="supabase_schema.sql" class="btn btn-outline-light">View Schema SQL</a>
    </div>
  </div>
</body>
</html>
