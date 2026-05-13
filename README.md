# Game Store

<p align="center">
  <img src="assets/banner.svg" alt="Game Store Banner" width="100%">
</p>

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![XAMPP](https://img.shields.io/badge/XAMPP-Local%20Dev-FB7A24?style=for-the-badge)
![Git LFS](https://img.shields.io/badge/Git%20LFS-Enabled-0B84FF?style=for-the-badge)

A full-featured PHP and MySQL game store built for XAMPP.  
It includes browsing, game details, wishlist and library management, user profiles, collections, reviews, notifications, email verification, and password reset.

## Highlights

- Clean homepage with game cards and featured content
- Browse page with search, filters, and sorting
- Game details page with related games and trailers
- Wishlist and library system for signed-in users
- Profile page with editable user information
- Recently viewed games tracking
- Review and rating system
- Custom collections for organizing games
- Notifications center for important activity
- Email verification and password reset flows
- Light and dark theme toggle
- Admin dashboard for managing games and users

## What Makes It Cool

- Trailer videos are included for a richer game-store feel
- User activity is tracked so the site feels more personal
- Collections let users organize games in a way that suits them
- Reviews and ratings make the store feel interactive
- Notifications help users stay aware of account and content changes
- Theme switching gives the site a more modern feel

## Tech Stack

- PHP
- MySQL / MariaDB
- HTML
- CSS
- JavaScript
- XAMPP
- Git LFS for large video assets

## Project Structure

- `index.php` - homepage
- `browse.php` - browse and filter games
- `view_game.php` - game details page
- `profile.php` - user profile and account settings
- `collections.php` - custom collections
- `wishlist.php` - saved games
- `library.php` - owned or added games
- `notifications.php` - user notifications
- `setup_database.php` - database installer and upgrader
- `admin/` - admin panel and management tools
- `images/` - game cover images
- `Video/` - trailer videos

## Features In Detail

### User Features

- Register and log in
- Email verification
- Password reset
- Update profile details
- Add games to wishlist
- Add games to library
- Create and manage collections
- Leave reviews and star ratings
- View recently opened games

### Store Features

- Browse all games
- Search by title or keyword
- Filter and sort game listings
- View related games on the details page
- Watch trailers directly from the store

### Admin Features

- Add new games
- Edit existing games
- Delete games
- View user data
- Manage store content

## Quick Start

1. Create the Supabase database using `supabase_schema.sql`.
2. Add the Render environment variables from `.env.example`.
3. Deploy the PHP app to Render.
4. Open the Render URL in your browser.

Example local URL:

```text
http://localhost/Game_Store/
```

## Live Demo

This project is meant to run on Render with Supabase as the database.

- Live app: your Render URL
- Database: your Supabase project

## Deployment Notes

This repo now reads its database settings and public base URL from environment variables.

Copy `.env.example` to your own environment file and set:

- `APP_BASE_URL` to your live site URL
- `DB_PASSWORD`
- Optional: `SUPABASE_PROJECT_REF` and `SUPABASE_REGION` if you want the app to build the Supabase pooler URL automatically
- Optional: `SUPABASE_POOLER_MODE=session` is the safest default for Render
- `DATABASE_URL` is still supported and overrides the auto-built pooler URL when set

Important: the app is still PHP-rendered. Render can host it as the live web app using Docker, and Supabase is now the database layer.

Vercel is not the right primary host for this PHP codebase unless we rewrite the frontend into a JavaScript app.

On Render, the service needs to run from the repo's `Dockerfile`, not the native PHP runtime.

## Default Admin Login

- Username: `admin`
- Password: `admin123`

## Database Setup

The project uses `setup_database.php` to create the database schema and apply upgrades.

Run it again if you:

- add a new table
- rename a column
- update the login or profile schema
- change the seed data

## Git LFS Notes

Large trailer files are stored with Git LFS so the repository can stay on GitHub.

If you clone this project on another machine, run:

```bash
git lfs install
git lfs pull
```

## Screenshots

Add your best project screenshots here to make the repository look more professional:

- Homepage
- Browse page
- Game details page
- Profile page
- Admin dashboard

## Recommended Improvements

- Add more game categories
- Add a favorites section
- Add admin review moderation
- Add notifications for price or content updates
- Add richer analytics in the admin panel

## Troubleshooting

- If the profile page shows a missing column error, re-run `supabase_schema.sql`.
- If trailer files do not appear after cloning, make sure Git LFS is installed.
- If deployed links still point to localhost, check `APP_BASE_URL`.
- If the live site cannot connect to the database, confirm `DB_PASSWORD` is set in Render and that `SUPABASE_PROJECT_REF` matches your Supabase project.
- If you see `Network is unreachable`, keep `SUPABASE_POOLER_MODE=session` and redeploy so the app uses the IPv4-friendly Supabase pooler.
- If the host is still `localhost`, set `SUPABASE_PROJECT_REF` or `DB_HOST` to your Supabase database host.
- If Render says `php: command not found`, recreate the service as a Docker-based web service using this repo's `Dockerfile`.

## License

This project is for personal and educational use unless you add a license file.
