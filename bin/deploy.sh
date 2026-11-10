#!/usr/bin/env bash
# Deploy on the server: pull the latest code and make sure WordPress uses it.
# Run it on the VPS from the cloned repo:   bin/deploy.sh
#
# One-time setup on the server: create bin/.deploy.env (git-ignored) with the path to the live WordPress, e.g.
#   WP_PATH="/var/www/intelladigital.com"
# Optional extras in the same file:
#   BRANCH="main"                              # Branch to deploy
#   AFTER_DEPLOY="sudo systemctl reload php8.3-fpm"   # Only if PHP's opcache doesn't pick up changed files
#
# What it does: git pull → links the theme and mu-plugins into wp-content (first run only) → clears caches.
# It never touches the database or uploads, so content written in wp-admin is safe.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
[ -f "$ROOT/bin/.deploy.env" ] && source "$ROOT/bin/.deploy.env"
: "${WP_PATH:?Set WP_PATH in bin/.deploy.env (the folder that holds wp-config.php)}"
BRANCH="${BRANCH:-main}"

cd "$ROOT"
echo "==> Pulling $BRANCH"
git fetch --quiet origin "$BRANCH"
git merge --ff-only "origin/$BRANCH"
echo "    Now at: $(git log -1 --format='%h %s')"

echo "==> Linking theme and mu-plugins"
CONTENT="$WP_PATH/wp-content"
mkdir -p "$CONTENT/themes" "$CONTENT/mu-plugins"
ln -sfn "$ROOT/theme/intella" "$CONTENT/themes/intella"
for item in "$ROOT"/mu-plugins/*; do   # One link per item, so the host's own mu-plugins stay untouched.
	ln -sfn "$item" "$CONTENT/mu-plugins/$(basename "$item")"
done

if command -v wp >/dev/null 2>&1; then
	echo "==> Clearing WordPress caches"
	wp --path="$WP_PATH" cache flush --quiet || true
	wp --path="$WP_PATH" rewrite flush --quiet || true
	# Page-cache plugins: add their purge command here once one is installed.
fi

if [ -n "${AFTER_DEPLOY:-}" ]; then
	echo "==> $AFTER_DEPLOY"
	eval "$AFTER_DEPLOY"
fi

echo "Done."
