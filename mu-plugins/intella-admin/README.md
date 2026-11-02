# Intella Admin (`/admin/`)

The Intella Digital team panel: project boards (cards, comments, bug tracker, goals, notes) for client and
in-house work, plus an Admins page that controls who can open it. It replaces the boards in the project hubs
(`Intella Digital/index.html` and `clients/Bizticle/index.html`), which stored their cards in `tracker-data.js`.

It is a **must-use plugin that works on its own**: it doesn't need the new theme or `intella-core`, so it can
run on the live (version 1) site today and on version 2 later, with the same data.

## Pages
| URL | What it shows |
|---|---|
| `/admin/` | Dashboard: every board with open cards, overdue cards and open bugs; "Add board" |
| `/admin/boards/<slug>/` | A board: Cards and Bug tracker tabs, Goals and Notes, "Download backup", "Import tracker-data.js" |
| `/admin/admins/` | Everyone with access, their board name and colour; add or remove people |

Visitors who aren't logged in go to the WordPress login and come back afterwards.

## Who can open it
- **WordPress administrators** have access automatically (on the live site: Amit, Moeez and Imran).
- **Future employees** are added on the Admins page. They get an **Intella Team** account that can open the
  panel but can't change the website. They receive an email with a link to set their password.
- Only WordPress administrators can add or remove people, and import boards. Removing someone only takes away
  panel access; their WordPress account stays.
- The **board name** (e.g. "Amit") is how a person appears on cards and bugs. Amit and Moeez get blue and green
  automatically when their name matches; otherwise set it on the Admins page.

## Data
Five tables (with the site's table prefix): `intella_boards`, `intella_lists`, `intella_cards`,
`intella_comments`, `intella_bugs`. They are created automatically on the first page load after the plugin is
uploaded (and updated whenever `INTELLA_ADMIN_VERSION` changes). Every edit on a board is saved straight away
through the REST API (`/wp-json/intella-admin/v1/`). Open boards reload every minute and when the browser tab
regains focus, so teammates see each other's changes.

New installs start with two empty boards, **Intella Digital** and **Bizticle**. To bring in the cards from a
project hub, open the board and click **Import tracker-data.js**. Importing **replaces** everything on that
board with the file's contents. **Download backup** saves the board as a JSON file in the same format, which
can be imported again.

From the command line (local site): `bin/wp intella-admin import <path to tracker-data.js> --board=<slug>`

## Deploy to the live server
Requirements: PHP 8.0 or newer, pretty permalinks (the live site already uses them).

1. Upload `mu-plugins/intella-admin.php` **and** the `mu-plugins/intella-admin/` folder (without this
   README if you like) into the live site's `wp-content/mu-plugins/` folder. On the live server WordPress is in
   `/wp/`, so the folder is `public_html/wp/wp-content/mu-plugins/`. Create `mu-plugins` if it doesn't exist.
   Don't upload `intella-core.php`: it belongs to the new theme and would clash with the live theme's case studies.
2. Open https://www.intelladigital.com/admin/ and log in. The tables, the Intella Team role and the two boards
   are created on this first visit.
3. On **Admins**, check everyone's board name and colour (Amit's live login is `admin`, so set his board name
   to "Amit" and colour to Blue if it isn't already). Remove anyone who shouldn't have access.
4. On each board, click **Import tracker-data.js** and pick that hub's latest file
   (`Intella Digital/tracker-data.js`, `clients/Bizticle/tracker-data.js`). Pull from git first so you import
   the newest version.
5. From then on, edit cards in the panel only. The hub boards in `index.html` no longer see these changes.

To update the panel later, upload the changed files over the old ones. To remove it, delete both from
`mu-plugins/`; the tables stay in the database until you drop them.

## Files
| File | What it does |
|---|---|
| `../intella-admin.php` | Loader: plugin header, version, includes |
| `access.php` | Capability, Intella Team role, board names and colours, grant / revoke |
| `data.php` | Reading and writing boards, lists, cards, comments, bugs; sanitising |
| `install.php` | Tables (dbDelta), role, starting boards, Amit / Moeez colours |
| `import.php` | Import and export in the tracker-data.js format; WP-CLI command |
| `rest.php` | REST API used by the board page |
| `actions.php` | Form posts: add board, add / update / remove admins |
| `router.php` | `/admin/` URLs, login redirect, access check, page rendering |
| `views/` | Page templates (layout, dashboard, board, admins, no access, not found) |
| `assets/` | `css/admin.css`; `js/board.js` (board page), `rte.js` (rich-text editor), `api.js`, `confirm.js` |

## Known limits (v0.1)
- Lists can't be added, renamed or reordered in the panel yet (new boards get the standard six lists; imported
  boards keep the hub's lists).
- Dragging cards needs a mouse. On a phone, open the card and change its **List** instead.
- If two people edit the same card field at the same moment, the last save wins.
- The hubs' Deadlines, Calendar, Bookmarks and Logins sections weren't moved; only the boards were.
