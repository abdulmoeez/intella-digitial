# Intella Digital — Version 2 (local setup)

The new intelladigital.com: theme in `theme/intella/`, site functionality in `mu-plugins/`.
Coding rules: [CONVENTIONS.md](CONVENTIONS.md).

## How it runs locally
- **Web server:** PHP's built-in server, using XAMPP's PHP 8.2. It runs as your user, so it can read this folder
  directly (XAMPP's Apache can't, because it runs as a different user without access to your home folder).
- **Database:** XAMPP's MySQL (MariaDB 10.4), database `intella_v2`, user `root`, no password.
- **WordPress:** downloaded into `wordpress/` (git-ignored). `theme/intella` and `mu-plugins/` are symlinked into
  `wordpress/wp-content/`, so you edit them here and refresh the browser.
- **WP-CLI:** `~/.wp-cli/wp-cli.phar`, run through `bin/wp`.

## Daily use
1. Start **MySQL Database** in the XAMPP manager (Apache isn't needed).
2. In a terminal:
   ```bash
   cd "/Users/flake/Work/Claude/Intella/intella/Intella Digital/Code/version 2"
   ```
   ```bash
   bin/start.sh
   ```
3. Open http://localhost:8080. Admin: http://localhost:8080/wp-admin/ (username and password are in `.env.local`).
4. Stop the server with **Ctrl+C**.

## First-time setup (new machine)
```bash
bin/setup.sh
```
It downloads WordPress and WP-CLI, creates the database, writes a local `wp-config.php` (debug on, file editor
off), installs WordPress, links and activates the theme, sets permalinks to `/articles/%postname%/`, creates
Home and Articles pages, and saves a random admin login to `.env.local`. Safe to run again.

## Useful commands
| Command | What it does |
|---|---|
| `bin/wp plugin install rank-math --activate` | Install a plugin (ask Amit first) |
| `bin/wp post list --post_type=page` | List pages |
| `bin/wp rewrite flush` | Rebuild permalinks after adding a post type |
| `tail -f wordpress/wp-content/debug.log` | Watch PHP errors |
| `bin/wp db export ~/Desktop/intella_v2.sql` | Back up the local database (never into this folder) |

## Settings
Paths, database name and port live in `bin/config.sh`. To override one on your machine, add it to `.env.local`
(for example `PORT=8090`).

## Start over
Delete `wordpress/` and `.env.local`, drop the database
(`/Applications/XAMPP/xamppfiles/bin/mysql -uroot -e "DROP DATABASE intella_v2"`), then run `bin/setup.sh`.

## Custom blocks (build step)
Section blocks are native WordPress blocks. Their source is in `theme/intella/src/blocks/` and WordPress loads the
compiled copy in `theme/intella/build/blocks/`. Node 22 is already installed (via nvm).
```bash
cd theme/intella && npm install
```
Then, while working on blocks, keep `npm run start` running (it rebuilds on save). Run `npm run build` before
committing. Global CSS and JS in `theme/intella/assets/` need no build.

## Deploying (git push → git pull on the VPS)
Design changes are theme files, so deploying is just moving code. Content (pages, posts, settings, uploads) lives
in the live database and is never touched by a deploy.

1. Claude changes the code; you check it on http://localhost:8080.
2. Commit and `git push`.
3. On the VPS, in the cloned repo:
   ```bash
   bin/deploy.sh
   ```
   It pulls the latest code, links `theme/intella` and `mu-plugins/` into the live `wp-content/` (first run
   only), and clears caches. CSS and JS URLs carry the file's modified time, so visitors get the new design
   straight away.

**One-time server setup:** clone the repo on the VPS outside the web root, install WordPress and WP-CLI there,
and create `bin/.deploy.env` (git-ignored) with `WP_PATH="/path/to/wordpress"` (the folder with `wp-config.php`).
If you build blocks, run `npm run build` locally and commit `build/`; the server never needs Node.
