"""Which prototype sections each block is built from (by their <!-- ===== name ===== --> markers).
Used by css_split.py (to give each block its CSS) and by the extractors."""
import re
from lib import FRONTEND, top_sections


# Section shapes that map to a proper block wherever they appear (service pages): block -> test(marker, html).
SHAPES = {
    'problems':     lambda m, h: 'class="pmpfs"' in h and 'pmprobs' in h,
    'sign-strips':  lambda m, h: m.startswith('Signs') and 'class="pmsts"' in h,
    'signs':        lambda m, h: m.startswith('Signs') and 'class="signs__list"' in h,
    'offer':        lambda m, h: 'class="fixed__card"' in h,
    'case-study':   lambda m, h: '<article class="bxcase' in h,
    'why-intella':  lambda m, h: 'class="wpwhy"' in h and 'csteam' not in h,
}


def shape_of(marker, html):
    return next((name for name, test in SHAPES.items() if test(marker, html)), None)


def is_short_hero(marker, html):
    """The short-form hero with a slider (cmhc) or a single report picture (pmrep); other hero visuals stay custom."""
    return marker.startswith('Hero: short form') and ('class="cmhc"' in html or 'class="pmrep ' in html)

# block name -> list of (page or '*', section marker name). '*' = that section on every page.
# A section is chosen by its position on the page (0 = first) or by its marker name.
SOURCES = {
    'home-hero':       [('index.html', 0)],
    'logo-strip':      [('index.html', 1), ('*', 'Client logos')],
    'home-services':   [('index.html', 2)],
    'home-audience':   [('index.html', 3)],
    'home-work':       [('index.html', 4)],
    'testimonials':    [('index.html', 5)],
    'free-review':     [('index.html', 6)],
    'home-process':    [('index.html', 7)],
    'home-why':        [('index.html', 8)],
    'home-industries': [('index.html', 9)],
    'latest-articles': [('index.html', 10)],
    'faq':             [('index.html', 11), ('*', 'FAQ')],
    'home-contact':    [('index.html', 12)],
    'page-hero':       [('about.html', 0), ('services.html', 0)],
    'thanks-hero':     [('thank-you.html', 0)],
    'contact-hero':    [('contact.html', 0)],
    'tilt-cards':      [('thank-you.html', 1), ('contact.html', 1), ('about.html', 2)],
    'trust-grid':      [('thank-you.html', 2)],
    'quote-strip':     [('thank-you.html', 4), ('contact.html', 2), ('about.html', 5), ('services.html', 9)],
    'story':           [('about.html', 1)],
    'industry-tags':   [('about.html', 4)],
    'talk':            [('*', 'has:talk__panel')],
    'stats':           [('services.html', 1)],
    'goals':           [('services.html', 2)],
    'service-lines':   [('services.html', 3)],
    'packages':        [('*', 'has:class="packages"')],
    'compare':         [('*', 'has:class="compare"')],
    'tools':           [('services.html', 8)],
    'not-found':       [('404.html', 0)],
    'link-list':       [('404.html', 1)],
    'related-services': [('*', 'Related services')],
    'request-panel':   [('*', 'has:cta-panel acta')],
    'testimonials-spread': [('*', 'has:section--tight cmvo"')],
    'hero-short-form': [('*', lambda m, h: is_short_hero(m, h))],
    **{name: [('*', test)] for name, test in SHAPES.items()},
    'service-hero':    [('*', 'Page hero with proposal form')],
    'get-in-touch':    [('*', 'Get in touch: testimonial + proposal form')],
}
SOURCES['home-why'] = SOURCES['home-why'] + [('about.html', 3)]
SOURCES['home-industries'] = SOURCES['home-industries'] + [('services.html', 7)]
SOURCES['latest-articles'] = SOURCES['latest-articles'] + [('thank-you.html', 5)]


def pages():
    return [FRONTEND / 'index.html'] + sorted((FRONTEND / 'pages').glob('*.html'))


def fragments(page_file, which):
    found = top_sections(page_file.read_text(encoding='utf-8'))
    if isinstance(which, int):
        return [found[which][1]] if which < len(found) else []
    if callable(which):  # a test on (marker, html)
        return [html for marker, html in found if which(marker, html)]
    if which.startswith('has:'):
        return [html for marker, html in found if which[4:] in html]
    return [html for marker, html in found if marker == which]


def collect(sources):
    out = []
    for page, name in sources:
        files = pages() if page == '*' else [FRONTEND / page if page == 'index.html' else FRONTEND / 'pages' / page]
        for f in files:
            out += fragments(f, name)
    return out


BLOCKS = {name: {'html': collect(src)} for name, src in SOURCES.items()}
