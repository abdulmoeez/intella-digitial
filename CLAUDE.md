# Version 2 — the new Intella Digital website

This folder is the new site: a WordPress theme built from scratch (`theme/intella/`) plus site functionality in
`mu-plugins/`. `Code/version 1/` is the old live site and is **reference only**: read it, never edit or copy it.

Follow the rulebook below for every line of code. Amit owns it and may change it at any time.

@CONVENTIONS.md

## Quick facts
- Local site: http://localhost:8080 (start with `bin/start.sh`; full steps in `README.md`). Admin login is in
  `.env.local` (git-ignored).
- WP-CLI: `bin/wp <command>` (for example `bin/wp plugin list`).
- PHP errors go to `wordpress/wp-content/debug.log`.
- Design frames: `../../Design/` (approved redesign frames are the source of truth once they exist).
- Content plan: `../../Content/Intella Digital Content Structure.xlsx`.
- URL changes are logged in `redirects.md`.
