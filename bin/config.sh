# Shared settings for the local dev scripts. Sourced by setup.sh, start.sh and wp.
# Change values here (or override them in .env.local) if your machine differs.

XAMPP="/Applications/XAMPP/xamppfiles"
PHP="${PHP:-$XAMPP/bin/php}"
MYSQL="${MYSQL:-$XAMPP/bin/mysql}"
MYSQLADMIN="${MYSQLADMIN:-$XAMPP/bin/mysqladmin}"

DB_NAME="${DB_NAME:-intella_v2}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-}"
DB_HOST="${DB_HOST:-127.0.0.1}"

PORT="${PORT:-8080}"
SITE_URL="http://localhost:$PORT"

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
WP_DIR="$ROOT/wordpress"
WP_CLI="$HOME/.wp-cli/wp-cli.phar"   # Outside the project: WP-CLI breaks on paths with spaces.

# Local overrides and the generated admin login.
[ -f "$ROOT/.env.local" ] && source "$ROOT/.env.local"

# XAMPP's php.ini asks for a redis extension that isn't installed; hide that one startup warning.
php_quiet() { "$PHP" "$@" 2> >(grep -v "redis.so" >&2); }
