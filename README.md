# Game Store

A PHP and MySQL-based game store built for XAMPP. It includes browsing, wishlist/library management, user profiles, collections, reviews, notifications, email verification, and password reset flows.

## Features

- Game browsing and search
- Game details pages with related games
- Wishlist and library management
- User profiles with editable details
- Recently viewed games
- Reviews and star ratings
- Custom collections
- Notifications center
- Email verification
- Password reset
- Light and dark theme toggle
- Admin dashboard

## Requirements

- XAMPP or any PHP + MySQL stack
- PHP 8+ recommended
- MySQL / MariaDB

## Setup

1. Copy the project into your web root.
2. Start Apache and MySQL in XAMPP.
3. Open `setup_database.php` once in the browser to create and upgrade the database tables.
4. Open the project in your browser.

## Default Admin Login

- Username: `admin`
- Password: `admin123`

## Notes

- This project is built for local development with XAMPP.
- Verification and password reset links are shown on screen in local mode unless SMTP is configured.
- If you change the database schema, run `setup_database.php` again.

## Main Files

- `index.php` - homepage
- `browse.php` - browse and filter games
- `view_game.php` - game details page
- `profile.php` - user profile page
- `collections.php` - custom collections
- `notifications.php` - notifications center
- `setup_database.php` - database installer and migration script

## License

This project is for personal/educational use unless you add a license.
