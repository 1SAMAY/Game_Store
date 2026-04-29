<?php if (is_admin()): ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark anime-navbar">
  <div class="container-fluid">
    <a class="navbar-brand anime-shine" href="dashboard.php">Admin Panel</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0 anime-nav-list">
        <li class="nav-item"><a class="nav-link anime-shine" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link anime-shine" href="add_game.php">Add Game</a></li>
        <li class="nav-item"><a class="nav-link anime-shine" href="manage_users.php">Users</a></li>
        <li class="nav-item"><a class="nav-link anime-shine" href="stats.php">Stats</a></li>
        <li class="nav-item"><a class="nav-link anime-shine" href="manage_games.php">Manage Games</a></li>
      </ul>
      <span class="navbar-text me-3 anime-fade">Hello, <?= htmlspecialchars($_SESSION['admin_user'] ?? '') ?></span>
      <a href="logout.php" class="btn btn-outline-danger anime-shine-btn">Logout</a>
    </div>
  </div>

  <style>
    /* Navbar initial animation: fade & slide from top */
    .anime-navbar {
      animation: slideDownFade 0.8s ease forwards;
      opacity: 0;
      transform: translateY(-20px);
    }
    @keyframes slideDownFade {
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    /* Nav links fade in staggered */
    .anime-nav-list .nav-link {
      opacity: 0;
      animation: fadeInUp 0.6s ease forwards;
      animation-delay: 0.3s;
    }
    .anime-nav-list .nav-item:nth-child(1) .nav-link { animation-delay: 0.3s; }
    .anime-nav-list .nav-item:nth-child(2) .nav-link { animation-delay: 0.45s; }
    .anime-nav-list .nav-item:nth-child(3) .nav-link { animation-delay: 0.6s; }
    .anime-nav-list .nav-item:nth-child(4) .nav-link { animation-delay: 0.75s; }
    .anime-nav-list .nav-item:nth-child(5) .nav-link { animation-delay: 0.9s; }
    @keyframes fadeInUp {
      to {
        opacity: 1;
        transform: translateY(0);
      }
      from {
        opacity: 0;
        transform: translateY(10px);
      }
    }
    /* Shine effect on nav-link and brand */
    .anime-shine, .anime-shine-btn {
      position: relative;
      overflow: hidden;
      cursor: pointer;
      transition: color 0.3s ease;
    }
    /* Shine animation on hover */
    .anime-shine::before, .anime-shine-btn::before {
      content: '';
      position: absolute;
      top: 0; left: -75%;
      width: 50%;
      height: 100%;
      background: linear-gradient(120deg, transparent, rgba(255,255,255,0.7), transparent);
      transform: skewX(-20deg);
      transition: left 0.6s ease;
      pointer-events: none;
      z-index: 1;
    }
    .anime-shine:hover::before, .anime-shine-btn:hover::before {
      left: 125%;
    }
    /* Brand color highlight on hover */
    .anime-shine:hover, .anime-shine-btn:hover {
      color: #fff9c4;
      text-shadow: 0 0 8px #fff9c4;
    }
    /* Fade-in for welcome text */
    .anime-fade {
      opacity: 0;
      animation: fadeIn 1s ease forwards;
      animation-delay: 1.1s;
    }
    @keyframes fadeIn {
      from {opacity: 0;}
      to {opacity:1;}
    }
  </style>

  <script>
    // Optional: gentle scale effect on nav links on hover for more liveliness
    document.querySelectorAll('.anime-shine').forEach(elem => {
      elem.addEventListener('mouseenter', () => {
        elem.style.transform = 'scale(1.05)';
      });
      elem.addEventListener('mouseleave', () => {
        elem.style.transform = 'scale(1)';
      });
    });
    document.querySelectorAll('.anime-shine-btn').forEach(btn => {
      btn.addEventListener('mouseenter', () => {
        btn.style.transform = 'scale(1.05)';
      });
      btn.addEventListener('mouseleave', () => {
        btn.style.transform = 'scale(1)';
      });
    });
  </script>
</nav>
<?php endif; ?>
