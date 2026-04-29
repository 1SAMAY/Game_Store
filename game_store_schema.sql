-- Game Store schema and seed data for XAMPP / phpMyAdmin
-- Database name used by the app: game_store

CREATE DATABASE IF NOT EXISTS game_store
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE game_store;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(20) NOT NULL DEFAULT 'user',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY unique_username (username),
  UNIQUE KEY unique_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS games (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS library (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  game_id INT UNSIGNED NOT NULL,
  added_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY unique_library_game (game_id),
  CONSTRAINT fk_library_game
    FOREIGN KEY (game_id) REFERENCES games (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS wishlist (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  game_id INT UNSIGNED NOT NULL,
  added_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY unique_wishlist_game (game_id),
  CONSTRAINT fk_wishlist_game
    FOREIGN KEY (game_id) REFERENCES games (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO users (id, username, email, password, role) VALUES
(1, 'admin', 'admin@gamestore.local', '$2y$10$zyQ5I57wcCH4mO44/tAomO4hH0idM9Lw8yb3uhWKmXoKW7841FhEa', 'admin');

INSERT IGNORE INTO games
(id, title, image, description, category, price, media_url, min_requirement, max_requirement, story, rating)
VALUES
(1, 'Red Dead Redemption 2', 'images/RDR2.jpg', 'Outlaws for life in the dying days of the wild west.', 'Action / Adventure', 'Free', 'Video/RDR 2.mp4', 'OS: Windows 10; CPU: Intel i5-6600K; RAM: 12 GB; GPU: GTX 1060; Storage: 150 GB', 'OS: Windows 10; CPU: Intel i7-8700K; RAM: 16 GB; GPU: RTX 2070; Storage: 150 GB', 'Lead Arthur Morgan and the Van der Linde gang across a changing frontier.', 4.9),
(2, 'Black Myth: Wukong', 'images/Black Myth Wukong.jpeg', 'Unleash your legend in the mythical world of Sun Wukong.', 'Action / RPG', 'Free', 'Video/Black Myth Wukong Trailer .mp4', 'OS: Windows 10; CPU: Intel i5-8400; RAM: 16 GB; GPU: GTX 1060; Storage: 130 GB', 'OS: Windows 10; CPU: Intel i7-9700; RAM: 16 GB; GPU: RTX 3060; Storage: 130 GB', 'A mythic action RPG inspired by Journey to the West.', 4.8),
(3, 'Grand Theft Auto V', 'images/Gta V.jpg', 'Build an empire to stand the test of time.', 'Action / Adventure', 'Free', 'Video/GTA 5 Trailer .mp4', 'OS: Windows 10; CPU: Intel i5-3470; RAM: 8 GB; GPU: GTX 660; Storage: 110 GB', 'OS: Windows 10; CPU: Intel i7-4770; RAM: 16 GB; GPU: GTX 1060; Storage: 110 GB', 'Explore Los Santos in a modern open-world crime epic.', 4.7),
(4, 'VALORANT', 'images/Valorant.jpg', 'A 5v5 character-based tactical FPS.', 'Shooter / Tactical', 'Free', 'Video/VALORANT TRAILER.mp4', 'OS: Windows 10; CPU: Intel i3-4150; RAM: 4 GB; GPU: Intel HD 4000; Storage: 30 GB', 'OS: Windows 10; CPU: Intel i5-9400F; RAM: 8 GB; GPU: GTX 1050 Ti; Storage: 30 GB', 'Compete in precise team-based firefights.', 4.6),
(5, 'Far Cry 6', 'images/Far Cry 6.jpg', 'Fight against a modern-day dictatorship in the tropical island of Yara.', 'Shooter / Open World', 'Free', 'Video/Far Cry 6 TRAILER.mp4', 'OS: Windows 10; CPU: Intel i5-4460; RAM: 8 GB; GPU: GTX 960; Storage: 80 GB', 'OS: Windows 10; CPU: Intel i7-7700; RAM: 16 GB; GPU: GTX 1080; Storage: 80 GB', 'Lead a modern-day guerrilla revolution to liberate the island nation of Yara.', 4.2),
(6, 'ARK', 'images/ARK.jpeg', 'Survive, tame, and build in a prehistoric world.', 'Survival / Adventure', 'Free', 'Video/ARK.mp4', 'OS: Windows 10; CPU: Intel i5-2400; RAM: 8 GB; GPU: GTX 670; Storage: 60 GB', 'OS: Windows 10; CPU: Intel i7-4770; RAM: 16 GB; GPU: GTX 980; Storage: 60 GB', 'Survive on an island full of dinosaurs.', 4.0),
(7, 'Uncharted 4', 'images/Uncharted 4.jpg', 'A globe-trotting adventure of treasure and betrayal.', 'Action / Adventure', 'Free', 'Video/UNCHARTED-4 TRAILER.mp4', 'OS: Windows 10; CPU: Intel i5-8400; RAM: 8 GB; GPU: GTX 960; Storage: 70 GB', 'OS: Windows 10; CPU: Intel i7-8700; RAM: 16 GB; GPU: GTX 1070; Storage: 70 GB', 'Nathan Drake returns for one final hunt.', 4.5),
(8, 'Elden Ring', 'images/Elden-Ring.jpg', 'Explore a vast fantasy realm of danger and discovery.', 'Action / RPG', 'Free', 'Video/ELDEN RING TRAILER.mp4', 'OS: Windows 10; CPU: Intel i5-8400; RAM: 12 GB; GPU: GTX 1060; Storage: 60 GB', 'OS: Windows 10; CPU: Intel i7-8700; RAM: 16 GB; GPU: RTX 2060; Storage: 60 GB', 'Traverse the Lands Between and forge your path.', 4.9),
(9, 'Dead by Daylight', 'images/Dead-by-Daylight.png', 'Survive the horror or become the hunter.', 'Horror / Multiplayer', 'Free', 'Video/Dead by Daylight TRAILER.mp4', 'OS: Windows 10; CPU: Intel i3-4170; RAM: 8 GB; GPU: GTX 460; Storage: 50 GB', 'OS: Windows 10; CPU: Intel i7-3770; RAM: 16 GB; GPU: GTX 760; Storage: 50 GB', 'A multiplayer asymmetrical horror experience.', 4.1),
(10, 'GTA V Enhanced', 'icons/Grand Theft Auto V.png', 'Enhanced edition of the modern crime classic.', 'Action / Adventure', 'Free', 'Video/GTA 5 Trailer .mp4', 'OS: Windows 10; CPU: Intel i5-3470; RAM: 8 GB; GPU: GTX 660; Storage: 110 GB', 'OS: Windows 10; CPU: Intel i7-4770; RAM: 16 GB; GPU: GTX 1060; Storage: 110 GB', 'A refreshed version of Grand Theft Auto V.', 4.7),
(11, 'F1 25', 'icons/F125.JPEG', 'High-speed racing with the latest Formula 1 thrills.', 'Racing / Sports', 'Free', 'Video/F1-25 TRAILER.mp4', 'OS: Windows 10; CPU: Intel i5-9600K; RAM: 8 GB; GPU: GTX 970; Storage: 80 GB', 'OS: Windows 10; CPU: Intel i7-10700K; RAM: 16 GB; GPU: RTX 2060; Storage: 80 GB', 'Compete on the world''s fastest tracks.', 4.3),
(12, 'Stellar Blade', 'icons/stellar.png', 'Fast-paced sci-fi combat with cinematic style.', 'Action / RPG', 'Free', 'Video/Stellar Blade TRAILER.mp4', 'OS: Windows 10; CPU: Intel i5-7600K; RAM: 8 GB; GPU: GTX 1060; Storage: 50 GB', 'OS: Windows 10; CPU: Intel i7-9700K; RAM: 16 GB; GPU: RTX 2070; Storage: 50 GB', 'Battle across a devastated future Earth.', 4.4),
(13, 'Fortnite', 'images/Fortnite.jpg', 'A massive battle royale with constant updates.', 'Shooter / Battle Royale', 'Free', 'Video/Fortnite TRAILER.mp4', 'OS: Windows 10; CPU: Core i3-3225; RAM: 8 GB; GPU: Intel HD 4000; Storage: 30 GB', 'OS: Windows 10; CPU: Core i5-7300U; RAM: 16 GB; GPU: GTX 960; Storage: 30 GB', 'Drop in and survive to be the last one standing.', 4.6),
(14, 'Rocket League', 'icons/ROCKET.png', 'Soccer meets rocket-powered cars.', 'Sports / Racing', 'Free', 'Video/Rocket League TRAILER.mp4', 'OS: Windows 10; CPU: Intel i3-4005U; RAM: 4 GB; GPU: Intel HD 4000; Storage: 20 GB', 'OS: Windows 10; CPU: Intel i5-4690; RAM: 8 GB; GPU: GTX 1060; Storage: 20 GB', 'Score goals at high speed with your team.', 4.5),
(15, 'Football Manager 2024', 'icons/FM24.png', 'Take control of a club and shape its future.', 'Simulation / Sports', 'Free', 'Video/Football TRAILER.mp4', 'OS: Windows 10; CPU: Intel i3-530; RAM: 4 GB; GPU: Intel HD 4000; Storage: 7 GB', 'OS: Windows 10; CPU: Intel i5-9600; RAM: 8 GB; GPU: GTX 970; Storage: 7 GB', 'Manage tactics, transfers, and glory.', 4.2),
(16, 'Genshin Impact', 'icons/GEN.PNG', 'Explore a vibrant fantasy world full of elemental magic.', 'Action / RPG', 'Free', 'Video/Genshin Impact TRAILER.mp4', 'OS: Windows 10; CPU: Intel Core i5; RAM: 8 GB; GPU: GTX 1030; Storage: 30 GB', 'OS: Windows 10; CPU: Intel Core i7; RAM: 16 GB; GPU: RTX 2060; Storage: 30 GB', 'Embark on a journey across Teyvat.', 4.4),
(17, 'MotoGP 25', 'icons/motogp25.png', 'The next chapter of elite motorcycle racing.', 'Racing / Sports', 'Free', 'Video/MotoGP 25 Trailer.mp4', 'OS: Windows 10; CPU: Intel i5-2500K; RAM: 8 GB; GPU: GTX 960; Storage: 35 GB', 'OS: Windows 10; CPU: Intel i7-8700; RAM: 16 GB; GPU: RTX 2060; Storage: 35 GB', 'Race the official MotoGP season.', 4.1),
(18, 'Dying Light: The Beast', 'icons/DYING.png', 'Survive the undead in a brutal open world.', 'Horror / Survival', 'Free', 'Video/Dying Light TRAILER.mp4', 'OS: Windows 10; CPU: Intel i5-2500; RAM: 8 GB; GPU: GTX 780; Storage: 40 GB', 'OS: Windows 10; CPU: Intel i7-8700; RAM: 16 GB; GPU: GTX 1070; Storage: 40 GB', 'Parkour, survival, and zombies collide.', 4.2),
(19, 'Tides of Annihilation', 'icons/TIDES.png', 'A cinematic action journey through broken worlds.', 'Action / Adventure', 'Free', 'Video/Tides of Annihilation TREILER.mp4', 'OS: Windows 10; CPU: Intel i5-8400; RAM: 8 GB; GPU: GTX 1060; Storage: 50 GB', 'OS: Windows 10; CPU: Intel i7-10700; RAM: 16 GB; GPU: RTX 2060; Storage: 50 GB', 'Fight through a mythic world under siege.', 4.0),
(20, 'MONGIL: STAR DIVE', 'icons/MONGIL.png', 'A fantasy adventure with team-based combat.', 'RPG / Adventure', 'Free', 'Video/MONGIL TREILER.mp4', 'OS: Windows 10; CPU: Intel i5-6400; RAM: 8 GB; GPU: GTX 960; Storage: 40 GB', 'OS: Windows 10; CPU: Intel i7-8700; RAM: 16 GB; GPU: RTX 2060; Storage: 40 GB', 'A colorful adventure in a living fantasy universe.', 4.0),
(21, 'Resident Evil Requiem', 'icons/RDE.png', 'Classic survival horror with modern intensity.', 'Horror / Survival', 'Free', 'Video/Resident Evil Trailer.mp4', 'OS: Windows 10; CPU: Intel i5-4460; RAM: 8 GB; GPU: GTX 760; Storage: 40 GB', 'OS: Windows 10; CPU: Intel i7-7700; RAM: 16 GB; GPU: GTX 1070; Storage: 40 GB', 'Face an all-new nightmare.', 4.5);

