# Intella Digital — WordPress Theme

This folder is a **complete WordPress theme** converted from the static HTML site in the project root. The original HTML/CSS/JS files were **not modified**.

## Install

1. Copy the entire `wordpress` folder to your WordPress themes directory:
   ```
   wp-content/themes/intella-digital/
   ```
   (Rename the folder to `intella-digital` or any slug you prefer.)

2. In **Appearance → Themes**, activate **Intella Digital**.

3. Create pages and assign templates (see below).

4. Set **Settings → Reading → Your homepage displays → A static page** and choose the page using the Home / front-page layout.

## Page templates

| WordPress page slug (suggested) | Template file |
|--------------------------------|---------------|
| (home / front page) | `front-page.php` (automatic when set as static front page) |
| `about` | About |
| `services` | Services |
| `contact-us` | Contact |
| `portfolio` | Portfolio |
| `case-studies` | Case Studies |
| `case-studies/rbl-media` | Case Study Rbl |
| `blog` | Blogs (archive layout) |
| sample post | Blog Details |

Assign each page under **Page attributes → Template** in the editor.

## File structure

```
wordpress/
├── style.css              # Theme header (required by WordPress)
├── functions.php          # Enqueues CSS/JS
├── index.php              # Fallback
├── front-page.php         # Home page
├── page-templates/        # All other pages
├── includes/
│   ├── header.php         # <!DOCTYPE>, <head>, <body>
│   ├── top-navigation.php # Site header shell + nav items
│   ├── brand-logo.php     # Logo SVG (reused in header)
│   ├── footer.php         # Site footer
│   └── sections/          # Reusable blocks (marquee, testimonials, contact, CTA, etc.)
│       └── services-megamenu.php
└── assets/
    ├── styles.css
    ├── styles.res.css
    ├── func.js
    └── images/
```

## Reusable sections

These are included with `require_once` and used on multiple pages:

| File | Used on |
|------|---------|
| `sections/logo-marquee.php` | Home, Services |
| `sections/testimonials.php` | Home, Services |
| `sections/case-studies-teaser.php` | Home, About, Services |
| `sections/contact-section.php` | Home, About, Portfolio |
| `sections/contact-section-contact-page.php` | Contact |
| `sections/contact-expect-steps.php` | Contact |
| `sections/cta-banner.php` | Services, Case Studies, Case Study RBL, Blogs, Blog Details |
| `sections/related-case-studies.php` | Case Study RBL, Blog Details |

## Typical page template pattern

Every page follows this structure:

```php
<?php require_once get_template_directory() . '/includes/header.php'; ?>
<?php require_once get_template_directory() . '/includes/top-navigation.php'; ?>

<div class="your-page-container">
    <!-- page-specific sections -->
    <?php require_once get_template_directory() . '/includes/sections/contact-section.php'; ?>
</div>

<?php require_once get_template_directory() . '/includes/footer.php'; ?>
```

## Notes

- Update page slugs in `includes/top-navigation.php` and `includes/footer.php` if your WordPress permalinks differ.
- Contact forms are client-side only (same as static site); wire to Contact Form 7 or a custom handler as needed.
- Some image paths reference files that may need to be uploaded to `assets/images/` subfolders (About, portfolio, CaseStudies, etc.).
