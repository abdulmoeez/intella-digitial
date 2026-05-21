#!/usr/bin/env python3
"""Convert static HTML site to WordPress theme PHP structure."""

import re
import os
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
WP = Path(__file__).resolve().parent
SRC = ROOT

PAGE_MAP = {
    "home.html": ("front-page.php", "home-page-container", "Intella Digital"),
    "about.html": ("page-templates/template-about.php", "about-page-container", "About Us - Intella Digital"),
    "services.html": ("page-templates/template-services.php", "services-page-container", "Intella Digital - Services"),
    "contact-us.html": ("page-templates/template-contact.php", "contact-us-page-container", "Intella Digital - Contact Us"),
    "portfolio.html": ("page-templates/template-portfolio.php", "portfolio-page-container", "Portfolio - Intella Digital"),
    "case-studies.html": ("page-templates/template-case-studies.php", "case-studies-page-container", "Case Studies - Intella Digital"),
    "case-study-rbl.html": ("page-templates/template-case-study-rbl.php", "case-study-rbl-page-container", "Case Study - RBL Media - Intella Digital"),
    "blogs.html": ("page-templates/template-blogs.php", "blogs-page-container", "Blogs - Intella Digital"),
    "blog-details.html": ("page-templates/template-blog-details.php", "blog-details-page-container", "Blog - Intella Digital"),
}

URL_MAP = {
    "home.html": "<?php echo esc_url( home_url( '/' ) ); ?>",
    "about.html": "<?php echo esc_url( home_url( '/about/' ) ); ?>",
    "services.html": "<?php echo esc_url( home_url( '/services/' ) ); ?>",
    "contact-us.html": "<?php echo esc_url( home_url( '/contact-us/' ) ); ?>",
    "portfolio.html": "<?php echo esc_url( home_url( '/portfolio/' ) ); ?>",
    "case-studies.html": "<?php echo esc_url( home_url( '/case-studies/' ) ); ?>",
    "case-study-rbl.html": "<?php echo esc_url( home_url( '/case-studies/rbl-media/' ) ); ?>",
    "blogs.html": "<?php echo esc_url( home_url( '/blog/' ) ); ?>",
    "blog-details.html": "<?php echo esc_url( home_url( '/blog/sample-post/' ) ); ?>",
}

# Shared sections: (class marker, output filename)
SHARED_SECTIONS = [
    ("idlp-s2", "logo-marquee.php"),
    ("idlp-s7", "testimonials.php"),
    ("idlp-s8", "case-studies-teaser.php"),
    ("idlp-s9", "contact-section.php"),
    ("idsp-s9", "cta-banner.php"),
    ("idcup-s1", "contact-section-contact-page.php"),
    ("idcup-s2", "contact-expect-steps.php"),
    ("idcsRbl-s3", "related-case-studies.php"),
]


def asset_uri(path: str) -> str:
    """Replace assets/ paths with PHP template URI."""
    return (
        '<?php echo esc_url( get_template_directory_uri() . \'/assets/'
        + path.replace("assets/", "")
        + "' ); ?>"
    )


def transform_html(html: str) -> str:
    """Apply WordPress-friendly transforms."""

    def asset_repl(m: re.Match) -> str:
        attr, path = m.group(1), m.group(2)
        uri = "<?php echo esc_url( get_template_directory_uri() ); ?>"
        return f'{attr}="{uri}/assets/{path}"'

    html = re.sub(r'(src|href)="assets/([^"]+)"', asset_repl, html)

    for old, new in URL_MAP.items():
        html = html.replace(f'href="{old}"', f'href="{new}"')
        html = html.replace(f"href='{old}'", f"href='{new}'")

    # Nav placeholders — replace with WP page URLs where known
    nav_urls = {
        "About": "<?php echo esc_url( home_url( '/about/' ) ); ?>",
        "Case Studies": "<?php echo esc_url( home_url( '/case-studies/' ) ); ?>",
        "Portfolio": "<?php echo esc_url( home_url( '/portfolio/' ) ); ?>",
        "Resources": "<?php echo esc_url( home_url( '/blog/' ) ); ?>",
    }
    for label, url in nav_urls.items():
        html = html.replace(
            f'<a href="#">\n                            <p>{label}</p>',
            f'<a href="{url}">\n                            <p>{label}</p>',
        )
        html = html.replace(
            f'<a href="#">\n                            <div class="id-nav-item-div">\n                                <p>{label}</p>',
            f'<a href="{url}">\n                            <div class="id-nav-item-div">\n                                <p>{label}</p>',
        )

    return html


def extract_section(html: str, class_name: str):
    """Extract first <section class="class_name"...>...</section>."""
    pattern = rf'<section\s+class="{re.escape(class_name)}"[^>]*>'
    m = re.search(pattern, html)
    if not m:
        return None
    start = m.start()
    depth = 0
    i = m.start()
    while i < len(html):
        if html[i : i + 8] == "<section":
            depth += 1
        elif html[i : i + 10] == "</section>":
            depth -= 1
            if depth == 0:
                return html[start : i + 10]
        i += 1
    return None


def extract_between(html: str, start_marker: str, end_marker: str) -> str:
    s = html.find(start_marker)
    e = html.find(end_marker)
    if s == -1 or e == -1:
        return ""
    return html[s:e]


def read_html(name: str) -> str:
    return (SRC / name).read_text(encoding="utf-8")


def get_header_block(html: str) -> str:
    """Full site header element."""
    m = re.search(r'<header class="idlp-header">.*?</header>', html, re.DOTALL)
    return m.group(0) if m else ""


def get_footer_block(html: str) -> str:
    m = re.search(r'<footer class="idlp-footer">.*?</footer>', html, re.DOTALL)
    return m.group(0) if m else ""


def get_page_content(html: str, container_class: str) -> str:
    """Content between </header> and <footer> inside page container."""
    # Find container open
    pat = rf'<div class="{re.escape(container_class)}">'
    m = re.search(pat, html)
    if not m:
        return ""
    start = html.find("</header>", m.start())
    if start == -1:
        start = m.end()
    else:
        start += len("</header>")
    end = html.find('<footer class="idlp-footer">')
    if end == -1:
        return ""
    content = html[start:end].strip()
    return content


def replace_sections_with_includes(content: str) -> str:
    """Replace shared section HTML with PHP requires."""
    for class_name, filename in SHARED_SECTIONS:
        section = extract_section(content, class_name)
        if section:
            include = (
                f"<?php require_once get_template_directory() . '/includes/sections/{filename}'; ?>\n"
            )
            content = content.replace(section, include, 1)
    return content


def php_wrap_template(name: str, title: str, container: str, body: str) -> str:
    template_name = name.replace("page-templates/", "").replace(".php", "").replace("template-", "").title()
    header = f"""<?php
/**
 * Template Name: {template_name.replace('-', ' ').title()}
 * Description: Converted from static HTML - {name}
 */
?>
<?php require_once get_template_directory() . '/includes/header.php'; ?>
<?php require_once get_template_directory() . '/includes/top-navigation.php'; ?>

<div class="{container}">

{body}

</div>

<?php require_once get_template_directory() . '/includes/footer.php'; ?>
"""
    if name == "front-page.php":
        header = f"""<?php
/**
 * Front Page Template
 * Description: Home page - converted from home.html
 */
?>
<?php require_once get_template_directory() . '/includes/header.php'; ?>
<?php require_once get_template_directory() . '/includes/top-navigation.php'; ?>

<div class="{container}">

{body}

</div>

<?php require_once get_template_directory() . '/includes/footer.php'; ?>
"""
    return header


def main():
    home_html = read_html("home.html")
    header_raw = get_header_block(home_html)
    footer_raw = get_footer_block(home_html)

    sections_dir = WP / "includes" / "sections"
    sections_dir.mkdir(parents=True, exist_ok=True)

    # Extract shared sections from home.html (canonical source)
    for class_name, filename in SHARED_SECTIONS:
        section = extract_section(home_html, class_name)
        if not section and class_name == "idcup-s1":
            section = extract_section(read_html("contact-us.html"), class_name)
        if not section and class_name == "idcup-s2":
            section = extract_section(read_html("contact-us.html"), class_name)
        if not section and class_name == "idsp-s9":
            section = extract_section(read_html("services.html"), class_name)
        if not section and class_name == "idcsRbl-s3":
            section = extract_section(read_html("case-study-rbl.html"), class_name)
        if section:
            out = transform_html(section)
            (sections_dir / filename).write_text(
                f"<?php\n/** Section: {class_name} */\n?>\n{out}\n",
                encoding="utf-8",
            )
            print(f"  section: {filename}")

    # header.php = DOCTYPE + head + body open
    head_match = re.search(r"<!DOCTYPE.*?</head>", home_html, re.DOTALL)
    head = head_match.group(0) if head_match else ""
    head = head.replace('href="styles.css"', 'href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/styles.css"')
    head = head.replace('href="styles.res.css"', 'href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/styles.res.css"')
    head = head.replace(
        '<link href="https://fonts.googleapis.com',
        "<?php wp_head(); ?>\n    <link href=\"https://fonts.googleapis.com",
    )

    header_php = f"""<?php
/**
 * Document head and body open
 */
?>{head}
<body <?php body_class(); ?>>
"""

    (WP / "includes" / "header.php").write_text(header_php, encoding="utf-8")

    # top-navigation.php
    nav = transform_html(header_raw)
    (WP / "includes" / "top-navigation.php").write_text(
        f"<?php\n/** Site header navigation */\n?>\n{nav}\n",
        encoding="utf-8",
    )

    # footer.php
    footer = transform_html(footer_raw)
    footer_php = f"""<?php
/** Site footer */
?>
{footer}
<script src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/func.js"></script>
<?php wp_footer(); ?>
</body>
</html>
"""
    (WP / "includes" / "footer.php").write_text(footer_php, encoding="utf-8")

    # Page templates
    for html_file, (out_path, container, title) in PAGE_MAP.items():
        html = read_html(html_file)
        content = get_page_content(html, container)
        content = replace_sections_with_includes(content)
        content = transform_html(content)
        full = php_wrap_template(out_path, title, container, content)
        out_file = WP / out_path
        out_file.parent.mkdir(parents=True, exist_ok=True)
        out_file.write_text(full, encoding="utf-8")
        print(f"  page: {out_path}")

    print("Done.")


if __name__ == "__main__":
    main()
