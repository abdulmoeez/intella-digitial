#!/usr/bin/env bash
# One-time local setup. Safe to re-run: it skips steps that are already done.
#  1. Downloads WP-CLI (to ~/.wp-cli/) and WordPress (to wordpress/, git-ignored)
#  2. Creates the intella_v2 database in XAMPP's MySQL
#  3. Writes wp-config.php for local development and installs WordPress
#  4. Links theme/intella and mu-plugins/ into wp-content and activates the theme
#  5. Applies the live site's permalink structure and basic settings
set -euo pipefail
source "$(dirname "$0")/config.sh"
cd "$ROOT"

step() { printf '\n==> %s\n' "$1"; }

step "WP-CLI"
if [ ! -f "$WP_CLI" ]; then
	mkdir -p "$(dirname "$WP_CLI")"
	curl -fsSL -o "$WP_CLI" https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
fi
bin/wp cli version

step "WordPress core"
if [ ! -f "$WP_DIR/wp-load.php" ]; then
	bin/wp core download --skip-content
fi

step "Database $DB_NAME"
"$MYSQL" -u"$DB_USER" ${DB_PASS:+-p"$DB_PASS"} -h"$DB_HOST" \
	-e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

step "wp-config.php"
if [ ! -f "$WP_DIR/wp-config.php" ]; then
	bin/wp config create --dbname="$DB_NAME" --dbuser="$DB_USER" --dbpass="$DB_PASS" --dbhost="$DB_HOST" \
		--dbcharset=utf8mb4 --skip-check --extra-php <<'PHP'
define( 'WP_ENVIRONMENT_TYPE', 'local' );
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );          // Errors go to wp-content/debug.log.
define( 'WP_DEBUG_DISPLAY', false );
define( 'SCRIPT_DEBUG', true );
define( 'DISALLOW_FILE_EDIT', true );    // No theme/plugin editor in the admin.
define( 'WP_POST_REVISIONS', 10 );
PHP
fi

step "Admin login (.env.local)"
if [ -z "${WP_ADMIN_PASS:-}" ]; then
	WP_ADMIN_USER="intella-admin"
	WP_ADMIN_PASS="$(LC_ALL=C tr -dc 'A-Za-z0-9' </dev/urandom | /usr/bin/head -c 24 || true)"
	cat >> "$ROOT/.env.local" <<ENV
# Local-only admin login, created by bin/setup.sh. Git-ignored. Not used anywhere else.
WP_ADMIN_USER="$WP_ADMIN_USER"
WP_ADMIN_PASS="$WP_ADMIN_PASS"
ENV
	chmod 600 "$ROOT/.env.local"
fi

step "Install WordPress"
if ! bin/wp core is-installed 2>/dev/null; then
	bin/wp core install --url="$SITE_URL" --title="Intella Digital (local)" \
		--admin_user="$WP_ADMIN_USER" --admin_password="$WP_ADMIN_PASS" \
		--admin_email="admin@intella.test" --skip-email
fi

step "Link theme and mu-plugins"
mkdir -p "$WP_DIR/wp-content/themes" "$WP_DIR/wp-content/plugins"
ln -sfn ../../../theme/intella "$WP_DIR/wp-content/themes/intella"   # Relative, so moving the folder is safe.
[ -L "$WP_DIR/wp-content/mu-plugins" ] || { rm -rf "$WP_DIR/wp-content/mu-plugins"; ln -s ../../mu-plugins "$WP_DIR/wp-content/mu-plugins"; }
bin/wp theme activate intella

step "Settings"
bin/wp rewrite structure '/articles/%postname%/'
bin/wp option update blog_public 0            # Discourage search engines (local site).
bin/wp option update default_comment_status closed
bin/wp option update timezone_string 'UTC'
bin/wp post delete 1 2 --force >/dev/null 2>&1 || true   # "Hello world" and "Sample Page".

if [ -z "$(bin/wp post list --post_type=page --name=home --field=ID)" ]; then
	HOME_ID="$(bin/wp post create --post_type=page --post_title='Home' --post_name='home' --post_status=publish --porcelain)"
	BLOG_ID="$(bin/wp post create --post_type=page --post_title='Articles' --post_name='articles' --post_status=publish --porcelain)"
	bin/wp option update show_on_front page
	bin/wp option update page_on_front "$HOME_ID"
	bin/wp option update page_for_posts "$BLOG_ID"
fi
bin/wp rewrite flush

printf '\nDone. Start the site with bin/start.sh, then open %s\n' "$SITE_URL"
printf 'Admin: %s/wp-admin/ (login in .env.local)\n' "$SITE_URL"
