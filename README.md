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

## Section blocks
Each page section is a block in `theme/intella/blocks/<name>/` (`block.json`, `render.php`, `style.css`). No npm
or build step: save a file and refresh. In the editor, select a section and edit its content in the right sidebar.

Blocks used across the service pages: Hero with short form, Problems and fixes, Signs you need this (checklist or
numbered strips), Fixed-scope offer, Case study, Why Intella, Testimonials (editorial spread), Request panel,
Related services, Get in touch, FAQ and Client logos. Testimonials and client logos come from their own admin
screens, so one edit updates every page.

## Importing the prototype pages
The pages built in `Code/frontend` are moved into WordPress with the kit in `bin/import/` (see CONVENTIONS.md,
section 15). To rebuild everything the import covers so far:
```bash
cd bin/import && python3 urls.py && python3 extract_site.py && python3 extract_tools.py && python3 extract_pages.py && python3 css_split.py && cd ../..
```
```bash
bin/wp --user=intella-admin eval-file bin/import/import.php settings shared menus pages
```
Run it as an administrator (`--user`), or SVG logos are refused. It updates what's there; it never deletes.

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
There is no build step, so the server never needs Node.

**Email (form enquiries):** every form is saved under **Leads** in wp-admin and emailed to the address in
Settings → Intella site ("Send form enquiries to", else the admin email). On the server, mail goes out through
Mailgun's SMTP server once two constants are in `wp-config.php` (never in git; see `mu-plugins/intella-core/mail.php`):
```bash
wp config set INTELLA_SMTP_USER contact@mg.intelladigital.com
wp config set INTELLA_SMTP_PASS --prompt=value
wp eval 'var_dump( wp_mail( get_option( "admin_email" ), "Test from the website", "It works." ) );'
```
`bool(true)` means Mailgun accepted it. Failures are logged as "Intella mail failed: …" in the PHP error log.
