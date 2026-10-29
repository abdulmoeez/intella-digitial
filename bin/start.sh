#!/usr/bin/env bash
# Start the local site at http://localhost:8080. Stop it with Ctrl+C.
set -euo pipefail
source "$(dirname "$0")/config.sh"

if [ ! -f "$WP_DIR/wp-config.php" ]; then
	echo "WordPress isn't installed yet. Run bin/setup.sh first."
	exit 1
fi

if ! "$MYSQLADMIN" -u"$DB_USER" ${DB_PASS:+-p"$DB_PASS"} -h"$DB_HOST" ping >/dev/null 2>&1; then
	echo "MySQL isn't running. Open the XAMPP manager (manager-osx) and start MySQL Database, then try again."
	exit 1
fi

echo "Intella Digital (version 2) running at $SITE_URL  —  admin: $SITE_URL/wp-admin/  —  Ctrl+C to stop"
cd "$WP_DIR"
PHP_CLI_SERVER_WORKERS=4 php_quiet -S "localhost:$PORT" -t "$WP_DIR" "$ROOT/bin/router.php"
