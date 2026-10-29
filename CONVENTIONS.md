# Version 2 — Coding Conventions

**Owner:** Amit. **Reader:** Claude (and any developer working on Version 2).

This is the rulebook for all code in `Code/version 2/`. Claude reads it before writing any code (it's loaded
through `CLAUDE.md`). Edit it whenever you like: the latest version always wins. When a rule here and an
instruction in chat disagree, Claude follows the chat instruction and offers to update this file.

Items marked **❓ Decision needed** are open. Until you answer, Claude uses the default written next to it.
All open items are also listed at the end.

---

## 1. Version 1 vs Version 2

| | Version 1 (`Code/version 1/`) | Version 2 (`Code/version 2/`) |
|---|---|---|
| What | Backup of the live site (files + `db/intella_db.sql`) | The new site we're building |
| Theme | `blankslate` (customised BlankSlate) | `intella`, built from scratch |
| Status | **Reference only. Never edit, copy wholesale, or build on it.** | Source of truth for new code |

Claude may **read** Version 1 to learn: existing URLs, custom-field names, the `case-study` post type, and how
the contact form works. Background: `Context/2026-10-23-current-codebase.md`.

Version 1 problems that Version 2 must not repeat:
- One PHP template per page with the copy hard-coded (every text change needed a developer).
- 6,000+ line stylesheets and 7,000-line inline `<style>` blocks inside templates.
- jQuery for everything; 72 MB of images inside the theme.
- Hand-written SEO tags that left almost every page without a meta description.
- Hard-coded email addresses and secrets in theme code.

---

## 2. Folder structure

```
Code/version 2/
├── CLAUDE.md            Tells Claude to follow this file
├── CONVENTIONS.md       This rulebook
├── README.md            How to run the site locally
├── .gitignore
├── redirects.md         Log of every URL change and its 301
├── bin/                 Local dev scripts: setup.sh, start.sh, wp, router.php, config.sh
├── theme/intella/       ★ The theme (presentation only)
├── mu-plugins/          ★ Site functionality that must survive a theme change
│   ├── intella-core.php      Loader
│   └── intella-core/         Post types, custom fields, forms, schema helpers
└── wordpress/           Local WordPress install (git-ignored, created by bin/setup.sh)
```

Only the ★ folders are deployed to the server.

### Theme layout (`theme/intella/`)

```
theme/intella/
├── style.css            Theme header only. No CSS here.
├── theme.json           Design tokens (colours, font sizes, spacing) and editor settings
├── functions.php        Loads files from inc/. No logic here.
├── inc/                 PHP, one concern per file
│   ├── setup.php        Theme supports, menus, image sizes
│   ├── cleanup.php      Removes WordPress head clutter
│   ├── helpers.php      Small shared functions
│   ├── enqueue.php      CSS and JS loading
│   └── blocks.php       Registers every compiled block in build/blocks/
├── header.php, footer.php
├── index.php, page.php, single.php, archive.php, search.php, 404.php   (WordPress template hierarchy)
├── page-templates/      Custom page templates (rare; see section 4)
├── template-parts/      Reusable PHP partials (site header, footer, cards)
├── src/blocks/<name>/   Block source: block.json, index.js, edit.js, render.php, style.css, editor.css, view.js
├── build/blocks/<name>/ Compiled blocks (made by `npm run build`; never edit by hand)
├── package.json         Block build tooling (@wordpress/scripts)
└── assets/
    ├── css/             Global CSS (see section 7)
    ├── js/              Global JS (see section 8)
    ├── fonts/           Self-hosted .woff2 only
    └── images/          Theme UI only: logo, icons, decorative SVGs. No content images.
```

### Theme vs plugin: where code goes

| Goes in the **theme** | Goes in **`mu-plugins/intella-core/`** |
|---|---|
| Templates, template parts, blocks' HTML | Custom post types (`case-study`) and taxonomies |
| CSS, JS, fonts, theme images | Custom fields and their meta keys |
| Menus, image sizes, editor styles | Form handlers, email sending, CRM hand-off |
| | Shortcodes used inside content |
| | Schema / structured data generation |

Rule of thumb: if switching themes would break or lose **content**, it belongs in the plugin.

---

## 3. Local environment

See `README.md`. Short version: `bin/start.sh`, then open http://localhost:8080.
WordPress runs on XAMPP's PHP through PHP's built-in web server, with XAMPP's MySQL (MariaDB) as the database.
The theme and mu-plugins are symlinked into `wordpress/wp-content/`, so you edit them in `theme/` and
`mu-plugins/` and the local site updates straight away.

---

## 4. Page templates and content

**Content lives in WordPress, not in PHP.** Every heading, paragraph, list, FAQ, table and button label that a
marketer might change must be editable in the WordPress admin.

- Pages use the normal template hierarchy (`page.php`, `single.php`, …), which outputs `the_content()`.
- Landing pages are built by stacking reusable **section blocks** in the editor: hero with proposal form,
  feature grid, process steps, comparison table, FAQ, CTA banner, case-study cards, testimonials, logo strip.
  One block = one design section from `Design/version 2/`.
- A **custom page template** (`page-templates/`) is allowed only when the page *frame* differs (for example no
  header navigation on a paid-ads landing page). Name it after the layout, never the page:
  `template-landing.php` ✅, `laravel-api-development.php` ❌.
- Never create one template per page. Never hard-code page copy in PHP. The only strings allowed in PHP are
  interface text (e.g. "Read more", "Skip to content"), wrapped in translation functions.
- Shared pieces (header, footer, cards) are template parts, called with
  `get_template_part( 'template-parts/…', null, $args )`. Pass data through `$args`, not globals.
- Blog posts and case studies follow `Context/2026-10-22-wordpress-content-rules.md` and keep the existing
  custom-field names (`summary`, `faq_blog_post`, `case_study_metric_1_label`, …) so old content still works.

### Section blocks (decided 2026-10-29: native WordPress blocks)

- Each section is a **native block** in `src/blocks/<name>/`, named `intella/<name>` (e.g. `intella/faq`), in the
  "Intella sections" inserter category. Block folder names are kebab-case and describe the section, not a page.
- **Every block is dynamic:** the editor saves only attributes (`save` returns `null`) and `render.php` outputs the
  HTML. Changing a block's design is then a PHP/CSS change and never breaks saved content.
- `edit.js` gives a close preview with inline editing (`RichText`, `MediaUpload`, `InspectorControls`). Use
  `@wordpress/components` controls; no other UI libraries.
- Use `InnerBlocks` when a section should hold free content (e.g. a two-column text section), with an
  `allowedBlocks` list.
- `block.json` declares `style` (front-end CSS), `editorStyle` (editor-only tweaks) and `viewScriptModule` (front-end
  JS) so WordPress loads each file only where the block is used.
- Set `"supports"` deliberately: turn off colour, typography and spacing controls unless the design needs them, so
  editors can't drift from the design.
- Build: `npm install` once, `npm run start` while working on blocks, `npm run build` before committing. The
  compiled `build/` folder is committed so the theme can be uploaded as-is (see Q10).

**❓ Decision needed (Q2): editor.** Default: the block editor (Gutenberg) for pages, posts and case studies,
and drop the Classic Editor plugin used in Version 1.

---

## 5. URLs (slugs)

Every new page, post and case study slug must follow these rules.

1. Lowercase letters, numbers and hyphens only. No underscores, spaces, capitals, accents or symbols.
2. Short: **2–4 words is ideal, 5 maximum**. Lead with the main keyword from the page brief.
3. **No stop words** (list below), unless removing one changes the meaning. Every exception needs Amit's OK.
4. No dates, years or anything that goes stale (`2026`, `new`, `latest`).
5. Don't repeat the parent's words in a child slug when the parent already says it
   (live pages such as `/laravel/laravel-api-development/` stay as they are; new pages follow this rule).
6. Always end with a trailing slash: `/services/digital-marketing/seo/`.
7. **Never change a published URL without a 301 redirect.** Every change is logged in `redirects.md`
   (old URL → new URL → date → reason).

**Stop words to remove:** a, an, the, and, or, but, nor, of, for, to, in, on, at, by, with, from, into, about,
as, is, are, was, be, it, its, this, that, these, those, your, our, my, their, we, you, how, what, why, when,
where, which, who, do, does, can, will, should, plus filler like "really", "actually", "just", "very".
*Keep* `vs` (comparisons are a search pattern) and keep words that are part of the keyword (e.g. `hire`).

| ❌ Live today | ✅ Version 2 style |
|---|---|
| `/articles/react-in-2026-whats-new-whats-dead-and-what-actually-matters/` | `/articles/react-changes/` |
| `/articles/what-is-prop-drilling-in-react-and-how-to-avoid-it/` | `/articles/react-prop-drilling/` |
| `/articles/angularjs-vs-modern-angular-which-is-right-for-your-business-in-2026/` | `/articles/angularjs-vs-angular/` |
| `/case-studies/the-other-glasses-luxury-eyewear-e-commerce-experience-intella-digital/` | `/case-studies/the-other-glasses/` |
| `/articles/custom-website-vs-wordpress/` | ✅ already good |

**Existing URLs (decided 2026-10-29): keep every live URL as it is.** These rules apply to new pages only.
Version 2 must launch with exactly the same URLs as the live site (see `Context/2026-10-22-website-urls.md`),
except pages we deliberately retire, which get a 301 logged in `redirects.md`.

---

## 6. Permalinks and site structure

| Content | Pattern | Notes |
|---|---|---|
| Pages | `/<parent>/<child>/` (from the page hierarchy) | Set the parent page; WordPress builds the path |
| Service pages | `/services/<service-line>/<cluster>/<page>/` | Service lines: `digital-marketing`, `website-development` |
| Audits | `/audits/<audit>/` and `/audits/<audit>/<child>/` | |
| Blog posts | `/articles/<slug>/` | Permalink setting: `/articles/%postname%/` |
| Blog index | `/articles/` | A page set as the "Posts page" |
| Case studies | `/case-studies/<slug>/` | `case-study` post type, archive at `/case-studies/` |
| Tags | `/articles/tag/<tag>/` | `noindex, follow` |
| Attachment pages | off | WordPress default since 6.4; keep it off |

- The `/web-development/` path from the old cluster notes is **not** used.
- Location pages (e.g. `-los-angeles`) follow the live pattern of a suffix on the service slug until we decide
  otherwise.
- **WordPress location (decided 2026-10-29): site root.** Version 1 runs WordPress from `/wp/`; Version 2 installs
  it at the root (admin at `/wp-admin/`). Page URLs don't change. Media URLs move from `/wp/wp-content/uploads/…`
  to `/wp-content/uploads/…`, so the migration must rewrite them and 301 the old media paths.
- **❓ Decision needed (Q5): author and category archives.** Default: keep author pages (they help E-E-A-T once
  they have real bios) and remove the single "Uncategorized" category archive from the sitemap.

---

## 7. CSS

**Where it lives**
- Global CSS: `theme/intella/assets/css/`, loaded on every page in this order:
  1. `tokens.css`: CSS custom properties (colours, fonts, spacing, radii, shadows). The only place raw values
     live, together with `theme.json`.
  2. `base.css`: reset and default styles for HTML elements and typography.
  3. `layout.css`: container, section spacing, site header and footer.
  4. `components/*.css`: one file per shared component used on many pages (`button.css`, `card.css`, `form.css`).
  5. `utilities.css`: a handful of helpers (`.visually-hidden`, `.container`). Loaded last.
- Block CSS lives **with its block**: `src/blocks/<name>/style.css`, referenced from `block.json`. WordPress loads
  it only on pages that use the block. Editor-only fixes go in `editor.css`.
- No CSS in `style.css`, in `<style>` tags, or in `style=""` attributes. No CSS in PHP.

**How it's written**
- Plain modern CSS: custom properties, native nesting, `clamp()`, `:is()` / `:where()`, logical properties
  (`margin-inline`, `padding-block`) so an Arabic (RTL) version is possible later.
- **No framework and no Sass** (no Tailwind, Bootstrap). Global CSS in `assets/css/` has no build step; block CSS
  passes through the block build only because it sits next to the block's JS.
- Naming: **BEM**. `.card`, `.card__title`, `.card--featured`. One component per file; the file name matches the
  block name. State classes: `is-open`, `is-active`, `has-error`.
- Colours, fonts, spacing and radii come **only** from tokens: `color: var(--color-accent)`, never `#9b30ff`.
- Design tokens are defined once in `theme.json` (so editors see the same palette) and mapped to short names in
  `tokens.css`.
- **Mobile first:** base styles are for phones, then `min-width` media queries. Breakpoints:
  `40rem` (640 px), `64rem` (1024 px), `80rem` (1280 px).
- Units: `rem` for type and spacing, `px` only for borders and hairlines, `%` / `fr` for layout.
- No `!important` (utilities excepted), no ID selectors, nesting at most 3 levels deep.
- Every interactive element has a visible `:focus-visible` style. Animations respect `prefers-reduced-motion`.
- Target size: all global CSS under 60 KB uncompressed.

---

## 8. JavaScript

- **Front end: vanilla JavaScript, ES modules, no jQuery, no build step.** jQuery is never loaded on the front end.
- **Block editor code** (`src/blocks/<name>/edit.js`, `index.js`) is React/JSX using `@wordpress/*` packages, built
  with `@wordpress/scripts`. This is the only JavaScript that needs a build.
- Global entry: `assets/js/main.js`, loaded with `wp_enqueue_script_module()` (modules are deferred
  automatically). It only imports features and starts them.
- Features live in `assets/js/modules/<feature>.js` and export an `init…()` function. Each file is registered
  automatically as `@intella/<feature>`, so import it by that name:
  `import { initNavigation } from '@intella/navigation';`
- Block front-end JS lives in `src/blocks/<name>/view.js` (`viewScriptModule` in `block.json`) and loads only
  when the block is on the page. It follows the same vanilla rules as `assets/js/`.
- Select elements by `data-*` attributes (`[data-nav-toggle]`) or ARIA, **never by styling classes**. CSS and JS
  can then change independently.
- **Progressive enhancement:** content is visible and forms submit without JavaScript; JS only improves things.
- No inline `<script>` in templates. Pass data to JS with `data-*` attributes.
- Modern syntax: `const` / `let`, arrow functions, `async` / `await`, `fetch`. No global variables.
- Use event delegation for repeated items, `IntersectionObserver` for scroll effects, and passive listeners for
  scroll and touch.
- No new library (sliders, animation, analytics) without Amit's OK. Tracking scripts go through Google Tag Manager.
- Each exported function gets a one-line JSDoc comment.
- Target size: all global JS under 30 KB uncompressed.

---

## 9. PHP

- Follow the **WordPress Coding Standards** (tabs, spaces inside brackets, `snake_case` functions). PHP 8.1+.
- Prefix everything: functions `intella_`, constants `INTELLA_`, hooks `intella/…`, text domain `intella`.
  New meta keys use `_intella_` (existing Version 1 keys keep their names).
- `functions.php` only loads files from `inc/`. One concern per file, short files.
- In templates, prepare data at the top, then write HTML. Keep logic out of the markup.
- **Security:** escape late (`esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()`), sanitise early, check
  nonces and `current_user_can()` on every form and AJAX action, use `$wpdb->prepare()` for SQL.
- Wrap interface strings in `__()` / `esc_html_e()` with the `intella` text domain.
- Nothing hard-coded that belongs in settings: no email addresses, page IDs, API keys or full URLs. Use
  `home_url()`, `get_permalink()`, options, or constants in `wp-config.php`.
- Images: always `wp_get_attachment_image()` (adds `srcset`, `width`, `height`, lazy loading).

---

## 10. HTML and accessibility

- Semantic landmarks: `<header>`, `<nav>`, `<main id="main">`, `<footer>`; a skip link is the first element.
- **One `<h1>` per page** (the page title). Headings go in order: H2, then H3. Content blocks never output an H1.
- Links go somewhere; buttons do something. Every form field has a `<label>`.
- Meaningful `alt` text on content images; `alt=""` on decorative ones.
- Target **WCAG 2.2 AA**: colour contrast, keyboard access, visible focus, 24 px minimum touch targets.

---

## 11. SEO and AEO / GEO in code

- `title-tag` theme support; the theme never hand-writes `<title>`, meta description, canonical or Open Graph tags.
- **SEO plugin (decided 2026-10-29): Rank Math.** It owns titles, meta descriptions, canonicals, Open Graph,
  sitemaps, robots rules and redirects (use its Redirections module for the 301s in `redirects.md`).
- Rank Math also outputs the base schema (Organization, WebSite, Article, BreadcrumbList). Theme blocks add only
  what it doesn't know about, by extending Rank Math's schema graph (`rank_math/json_ld` filter) instead of printing
  separate JSON-LD: FAQPage from the FAQ block, Service on service pages.
- Breadcrumbs use Rank Math's `rank_math_the_breadcrumbs()` in the template, styled by the theme.
- Structured data we want (JSON-LD): Organization and WebSite site-wide, BreadcrumbList on child pages, Service on service
  pages, FAQPage from the FAQ block, Article on posts, and the case-study fields where useful.
  Breadcrumbs appear on every page below the top level.
- Tag archives `noindex, follow`. Local site always "Discourage search engines".
- Fast, clean HTML is part of SEO: see section 12.

---

## 12. Performance, images and fonts

- Budget on mobile: **LCP under 2.5 s, CLS under 0.1, INP under 200 ms.**
- Content images go in the Media Library, never in the theme. Upload WebP (or AVIF), max 2,560 px wide.
- Theme images: SVG where possible; no theme image over 100 KB.
- The hero image gets `fetchpriority="high"` and no lazy loading; everything below the fold is lazy.
- Fonts: self-hosted `.woff2` in `assets/fonts/`, at most 2 families and 4 files, `font-display: swap`, preload the
  main one. No Google Fonts requests. *(Font choice comes from the redesign.)*
- No sliders or carousels above the fold. No video autoplay with sound.

---

## 13. Forms and leads

- **❓ Decision needed (Q8): form handling.** Default: a small custom handler in `mu-plugins/intella-core/`
  (like Version 1's, but fixed) with a honeypot and Cloudflare Turnstile for spam, server-side validation, and
  hidden UTM / referrer fields so we know which campaign each lead came from. Alternative: a form plugin
  (Fluent Forms, Gravity Forms, WPForms).
- The recipient address comes from a setting or `wp-config.php` constant, never from code.
- Every form shows a clear success and error state and works without JavaScript.

---

## 14. Git

- Tracked: everything in `Code/version 2/` except what `.gitignore` excludes (`wordpress/`, `node_modules/`,
  `.env.local`, database dumps, uploads).
- **Never commit** passwords, API keys, `wp-config.php`, database dumps or `Code/version 1/`.
- Small commits with a clear message: what changed and why (`Add FAQ block with FAQPage schema`).
- **❓ Decision needed (Q9): branches.** Default: `main` is always working; each feature on its own branch
  (`feature/faq-block`), merged after Amit reviews.

---

## 15. How Claude works in Version 2

1. Before coding, read this file, the matching frame in `Design/version 2/`, and the page brief in
   `Content/Intella Digital Content Structure.xlsx`.
2. For anything bigger than a small fix, say which files will be created or changed **before** writing them.
3. Write code only in `theme/intella/` and `mu-plugins/`. Never edit `Code/version 1/` or WordPress core files.
4. Check the result on the local site (desktop and 375 px mobile width) and check
   `wordpress/wp-content/debug.log` for PHP notices.
5. Report what changed and what to check, in plain language.
6. **Ask first** before: adding a plugin, library, font or npm package; deviating from the design; changing a
   URL; or breaking a rule in this file.
7. When Amit agrees a new rule in chat, offer to add it here.

---

## Open decisions

**Decided 2026-10-29:** native WordPress blocks (section 4) · keep all live URLs (section 5) · WordPress at the
site root (section 6) · Rank Math (section 11) · build step for blocks only (sections 7–8).

| # | Topic | Default until answered |
|---|---|---|
| Q2 | Editor | Block editor; drop Classic Editor |
| Q5 | Author / category archives | Keep author pages; drop "Uncategorized" |
| Q8 | Form handling | Custom handler in intella-core |
| Q9 | Git branches | Feature branches, reviewed by Amit |
| Q10 | Hosting, staging and deploy | _Unknown_ |
| Q11 | Arabic / RTL version ever? | Not planned, but CSS stays RTL-ready |
