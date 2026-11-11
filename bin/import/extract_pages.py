"""Turn prototype pages into block content: data/pages/<name>.json = {title, path, blocks: [{name, attrs}]}.

Each extractor reads one prototype section's HTML and returns the block's attributes (its editable content).
Run from bin/import:  python3 extract_pages.py [page.html ...]   (default: every page in PAGES)
"""
import json, re, sys
from lib import read, url, text, inline, DATA, FRONTEND, top_sections, elements, inner
from blocks_map import is_short_hero, shape_of

EXTRACT = {}


def extractor(name):
    def wrap(fn):
        EXTRACT[name] = fn
        return fn
    return wrap


def find(pattern, html, group=1, default=''):
    m = re.search(pattern, html, re.S)
    return m.group(group) if m else default


def bg(html):
    """Section background (cream/white) and anchor id from the <section> tag."""
    tag = find(r'(<section[^>]*>)', html)
    return {'background': find(r'section--(cream|white)', tag, default='cream'), 'anchor': find(r'\sid="([^"]+)"', tag)}


def button(a_html):
    """(text, url) from an <a> tag."""
    return {'text': text(find(r'<a[^>]*>(.*?)</a>', a_html)), 'url': url(find(r'href="([^"]*)"', a_html))}


# ---------------------------------------------------------------- blocks

@extractor('faq')
def faq(h):
    items = [{'question': text(q), 'answer': inline(a)} for q, a in re.findall(
        r'<summary>(.*?)<span class="faq-item__icon".*?</summary>\s*<p class="faq-item__answer">(.*?)</p>', h, re.S)]
    actions = find(r'<div class="faq__actions">(.*?)</div>', h)
    b = button(actions) if actions else {'text': '', 'url': ''}
    return {**bg(h), 'title': inline(find(r'<h2[^>]*>(.*?)</h2>', h)), 'lead': text(find(r'<p class="section-lead">(.*?)</p>', h)),
            'buttonText': re.sub(r'<svg.*', '', b['text']).strip(), 'buttonUrl': b['url'], 'items': items,
            'buttonStyle': 'primary' if 'button--primary' in actions else 'outline'}



def icon_of(h):
    return find(r'#i-([a-z-]+)', h)


def media(src):
    """Image/video in the prototype → 'media:<path>' (the importer adds it to the Media Library and stores its ID)."""
    src = re.sub(r'^(\.\./)+', '', src or '')
    return f'media:{src}' if src else 0


def btn(a, style=None):
    href = find(r'href="([^"]*)"', a)
    cls = find(r'class="([^"]*)"', a)
    return {'text': text(re.sub(r'<svg.*?</svg>', '', find(r'<a[^>]*>(.*?)</a>', a, default=a), flags=re.S)), 'url': url(href),
            'style': style or ('primary' if re.search(r'button--(primary|light|accent)\b', cls) else 'outline')}


SHARED = {'testimonial': {}, 'client': {}}
REAL_PEOPLE = {'David Clarke', 'Andra-Simina Mocanu', 'Alex Barsuk'}


def testimonial(name, role, quote, photo='', project=''):
    """Remember a testimonial for the shared list; return its reference for a posts field."""
    item = SHARED['testimonial'].setdefault(name, {'name': name, 'role': role, 'quote': quote, 'photo': photo,
                                                   'placeholder': name not in REAL_PEOPLE})
    if photo and not item['photo']:
        item['photo'] = photo
    if project and not item.get('project'):
        item['project'] = project
    if len(quote) > len(item['quote']):
        item['short_quote'], item['quote'] = item['quote'], quote
    return f'post:testimonial:{name}'


@extractor('home-hero')
def home_hero(h):
    actions = find(r'<div class="hero__actions">(.*?)</div>', h)
    sets = re.findall(r'<ul class="deck-tools__set"[^>]*>(.*?)</ul>', h, re.S)
    cards = []
    deck = find(r'<ul class="deck"[^>]*>(.*?)<div class="deck-tools"', h)
    for i, li in enumerate(deck.split('<li class="deck__card"')[1:]):
        a = find(r'<h2 class="deck__title">(.*?)</h2>', li)
        cards.append({'icon': icon_of(find(r'<span class="icon-tile">(.*?)</span>', li)), 'title': text(a), 'url': url(find(r'href="([^"]*)"', a)),
                      'text': text(find(r'<p class="deck__text">(.*?)</p>', li)),
                      'tags': [text(t) for t in re.findall(r'<li>(.*?)</li>', find(r'<ul class="deck__tags"[^>]*>(.*?)</ul>', li))],
                      'tools': re.findall(r'deck-tool__name">([^<]+)<', sets[i]) if i < len(sets) else []})
    return {'title': inline(find(r'<h1[^>]*>(.*?)</h1>', h)), 'lead': text(find(r'<p class="hero__lead">(.*?)</p>', h)),
            'buttons': [btn(a) for a in re.findall(r'<a [^>]*>.*?</a>', actions, re.S)], 'cards': cards}


@extractor('logo-strip')
def logo_strip(h):
    first = find(r'<ul class="logo-strip__list[^"]*" role="list">(.*?)</ul>', h)
    clients = []
    for src, alt in re.findall(r'<img src="([^"]+)" alt="([^"]*)"', first):
        SHARED['client'].setdefault(alt, {'name': alt, 'logo': media(src)[6:], 'placeholder': True})
        clients.append(f'post:client:{alt}')
    return {'label': text(find(r'<p class="logo-strip__label">(.*?)</p>', h)), 'clients': clients}


@extractor('home-services')
def home_services(h):
    head = find(r'<div class="section-head section-head--split">(.*?)<div class="wwd"', h)
    tabs = re.findall(r'<button class="wwd__tab"[^>]*>(.*?)</button>', h)
    panels = re.findall(r'<div class="wwd__panel"[^>]*>(.*?)</div>\s*(?=<div class="wwd__panel"|</div>\s*</div>\s*</div>\s*</section>)', h, re.S)
    out = []
    for label, p in zip(tabs, panels):
        links = [{'title': text(t), 'description': text(d), 'url': url(u), 'tool': tool}
                 for u, tool, t, d in re.findall(r'<a class="wwd__link" href="([^"]+)"><span class="svc-link__logo" title="([^"]*)">.*?<b>(.*?)</b><small>(.*?)</small>', p, re.S)]
        cta = btn(find(r'<div class="wwd__cta">(.*?)</div>', p))
        out.append({'tab': text(label), 'title': inline(find(r'<h3 class="wwd__title">(.*?)</h3>', p)),
                    'lead': text(find(r'<p class="wwd__lead">(.*?)</p>', p)), 'text': text(find(r'<p class="wwd__text">(.*?)</p>', p)),
                    'links': links, 'ctaText': cta['text'], 'ctaUrl': cta['url']})
    b = btn(find(r'(<a class="button[^>]*>.*?</a>)', head))
    return {**bg(h), 'title': inline(find(r'<h2[^>]*>(.*?)</h2>', h)), 'lead': text(find(r'<p class="section-lead">(.*?)</p>', head)),
            'buttonText': b['text'], 'buttonUrl': b['url'], 'tabs': out}


@extractor('home-audience')
def home_audience(h):
    tiles = []
    for names in re.findall(r'data-reel="([^"]+)"', h):
        tiles.append({'clips': [{'video': f'media:assets/video/{n}.mp4', 'poster': f'media:assets/video/{n}.webp'} for n in names.split()]})
    chips = re.findall(r'<div class="aud__chip[^"]*"[^>]*>.*?<b>(.*?)</b><small>(.*?)</small>', h, re.S)
    steps = [{'problem': text(p), 'answer': text(a)} for p, a in re.findall(r'<p class="fix__problem">(.*?)</p>.*?<p class="step-fix__answer">(.*?)</p>', h, re.S)]
    b = btn(find(r'(<a class="button[^>]*aud__cta[^>]*>.*?</a>)', h))
    return {**bg(h), 'title': inline(find(r'<h2[^>]*>(.*?)</h2>', h)), 'lead': text(find(r'<p class="section-lead">(.*?)</p>', h)),
            'buttonText': b['text'], 'buttonUrl': b['url'], 'tiles': tiles,
            'chips': [{'title': text(t), 'text': text(x)} for t, x in chips], 'steps': steps,
            'toolsTitle': inline(find(r'<h3 class="tools__title">(.*?)</h3>', h)), 'toolsLead': text(find(r'<p class="tools__lead">(.*?)</p>', h)),
            'tools': re.findall(r'<li class="tools__item"[^>]*data-name="([^"]+)"', h)}


@extractor('home-work')
def home_work(h):
    head = find(r'<div class="section-head section-head--split">(.*?)<div class="finder"', h)
    b = btn(find(r'(<a class="button[^>]*>.*?</a>)', head))
    finder = []
    for label, chips in zip(re.findall(r'<button class="finder__tab"[^>]*>(.*?)</button>', h), re.findall(r'<ul class="finder__chips"[^>]*>(.*?)</ul>', h, re.S)):
        finder.append({'tab': text(label), 'chips': [{'label': text(re.sub(r'<span class="finder__count">.*?</span>', '', l)), 'url': url(u), 'count': find(r'finder__count">(\d*)<', l)}
                       for u, l in re.findall(r'<a class="finder__chip" href="([^"]+)">(.*?)</a>', chips, re.S)]})
    all_link = re.search(r'<a class="finder__all" href="([^"]+)">(.*?)<svg', h, re.S)
    work = [{'image': media(src), 'alt': alt, 'tag': text(tag), 'title': text(t), 'text': text(x)} for src, alt, tag, t, x in re.findall(
        r'<article class="work-card">.*?<img src="([^"]+)"[^>]*alt="([^"]*)".*?<span class="card-tag">(.*?)</span>\s*<h3[^>]*>(.*?)</h3>\s*<p class="work-card__text">(.*?)</p>', h, re.S)]
    cases = [{'tag': text(tag), 'title': text(t), 'text': text(x), 'linkText': text(lt), 'url': url(u)} for tag, t, x, u, lt in re.findall(
        r'<article class="case-card">\s*<span class="card-tag">(.*?)</span>\s*<h3[^>]*>(.*?)</h3>\s*<p class="case-card__text">(.*?)</p>\s*<a class="text-link" href="([^"]+)">(.*?)<svg', h, re.S)]
    return {**bg(h), 'title': inline(find(r'<h2[^>]*>(.*?)</h2>', h)), 'buttonText': b['text'], 'buttonUrl': b['url'],
            'finderLabel': text(find(r'<p class="finder__label">(.*?)</p>', h)), 'finder': finder,
            'allText': text(all_link.group(2)), 'allUrl': url(all_link.group(1)), 'work': work, 'cases': cases}


@extractor('testimonials')
def testimonials(h):
    spot = [testimonial(text(n), text(r), text(q), media(src)[6:])
            for src, n, r, q in re.findall(r'<figure class="spotlight[^"]*".*?<img src="([^"]+)".*?spotlight__name">(.*?)</p>\s*<p class="spotlight__role">(.*?)</p>.*?<blockquote class="spotlight__quote"><p>(.*?)</p>', h, re.S)]
    cards = []
    for fig in re.findall(r'<figure class="quote-card[^"]*" data-quote.*?</figure>', h, re.S):
        photo = find(r'<img src="([^"]+)"', fig)
        cards.append(testimonial(text(find(r'person__name">(.*?)</p>', fig)), text(find(r'person__role">(.*?)</p>', fig)),
                                 text(find(r'<blockquote><p>(.*?)</p>', fig)), media(photo)[6:] if photo else ''))
    return {**bg(h), 'title': inline(find(r'<h2[^>]*>(.*?)</h2>', h)).replace('<em>', '<em>'),
            'lead': inline(find(r'<p class="section-lead">(.*?)</p>', h)), 'spotlight': spot, 'cards': cards}


@extractor('free-review')
def free_review(h):
    return {**bg(h), 'title': inline(find(r'<h2[^>]*>(.*?)</h2>', h)), 'lead': text(find(r'<p class="offer__lead">(.*?)</p>', h)),
            'checks': [text(x) for x in re.findall(r'<li>(?:<svg.*?</svg>)?(.*?)</li>', find(r'<ul class="check-list"[^>]*>(.*?)</ul>', h), re.S)],
            'formTitle': text(find(r'form-head__title">(.*?)</p>', h)), 'formSub': text(find(r'form-head__sub">(.*?)</p>', h)),
            'buttonText': text(find(r'<button[^>]*type="submit"[^>]*>(.*?)<svg', h)),
            'trust': [text(x) for x in re.findall(r'<li>(?:<svg.*?</svg>)?(.*?)</li>', find(r'<ul class="form-trust"[^>]*>(.*?)</ul>', h), re.S)]}


@extractor('home-process')
def home_process(h):
    steps = []
    tabs = re.findall(r'<button class="journey__step[^"]*".*?journey__when">(.*?)</span>\s*<span class="journey__step-title">(.*?)</span>', h, re.S)
    cards = re.findall(r'<article class="journey__card.*?</article>', h, re.S)
    for (when, title), card in zip(tabs, cards):
        lists = re.findall(r'<p class="journey__label">(.*?)</p>\s*<ul[^>]*>(.*?)</ul>', card, re.S)
        steps.append({'when': text(when), 'title': text(find(r'<h3 class="journey__title">(.*?)</h3>', card)) or text(title), 'tab': text(title),
                      'icon': icon_of(find(r'journey__icon">(.*?)</span>', card)), 'text': text(find(r'<p class="journey__text">(.*?)</p>', card)),
                      'get': [text(x) for x in re.findall(r'<li>(.*?)</li>', lists[0][1])] if lists else [],
                      'need': [text(x) for x in re.findall(r'<li>(.*?)</li>', lists[1][1])] if len(lists) > 1 else []})
    labels = [text(l) for l, _ in re.findall(r'<p class="journey__label">(.*?)</p>\s*<ul[^>]*>(.*?)</ul>', cards[0], re.S)] if cards else []
    end = find(r'<div class="journey__end">(.*?)</div>', h)
    b = btn(find(r'(<a [^>]*>.*?</a>)', end))
    return {'title': inline(find(r'<h2[^>]*>(.*?)</h2>', h)), 'lead': text(find(r'<p class="section-lead">(.*?)</p>', h)), 'steps': steps,
            'getLabel': labels[0] if labels else '', 'needLabel': labels[1] if len(labels) > 1 else '',
            'endText': text(find(r'<p class="journey__end-text">(.*?)</p>', end)), 'buttonText': b['text'], 'buttonUrl': b['url']}


@extractor('home-why')
def home_why(h):
    b = btn(find(r'<div class="why__actions">(.*?)</div>', h))
    photos = [{'image': media(src), 'alt': alt} for src, alt in re.findall(r'<div class="team-carousel__slide".*?<img src="([^"]+)"[^>]*alt="([^"]*)"', h, re.S)]
    cards = [{'icon': icon_of(i), 'title': text(t), 'text': text(x), 'dark': 'bento__card--dark' in c}
             for c, i, t, x in re.findall(r'<li class="(bento__card[^"]*)">\s*<span class="icon-tile[^"]*">(.*?)</span>\s*<h3 class="bento__title">(.*?)</h3>\s*<p>(.*?)</p>', h, re.S)]
    return {**bg(h), 'title': inline(find(r'<h2[^>]*>(.*?)</h2>', h)), 'lead': text(find(r'<p class="section-lead">(.*?)</p>', h)),
            'buttonText': b['text'], 'buttonUrl': b['url'], 'photos': photos, 'cards': cards}


@extractor('home-industries')
def home_industries(h):
    sectors = [{'image': media(src), 'name': text(n)} for src, n in re.findall(r'<li class="sector".*?<img class="sector__img" src="([^"]+)".*?sector__name">(.*?)</span>', h, re.S)]
    stats = []
    for value, label, note in re.findall(r'<li class="proof-stat">\s*<p class="proof-stat__value">(.*?)</p>\s*<p class="proof-stat__label">(.*?)</p>\s*<p class="proof-stat__note">(.*?)</p>', h, re.S):
        stats.append({'number': find(r'data-count="([^"]+)"', value), 'suffix': text(re.sub(r'<span data-count.*?</span>', '', value)), 'label': text(label), 'note': text(note)})
    results = [{'icon': icon_of(i), 'value': text(v), 'label': text(l), 'who': text(w)} for i, v, l, w in re.findall(
        r'<li class="result-card">\s*<span class="result-card__icon">(.*?)</span>\s*<p class="result-card__value">(.*?)</p>\s*<p class="result-card__label">(.*?)</p>\s*<p class="result-card__who">(.*?)</p>', h, re.S)]
    trust = [{'icon': icon_of(i), 'title': text(t), 'text': text(x)} for i, t, x in re.findall(
        r'<li class="trust-item">\s*<span class="icon-tile">(.*?)</span>\s*<div><p class="trust-item__title">(.*?)</p><p class="trust-item__text">(.*?)</p>', h, re.S)]
    link = re.search(r'<a class="text-link" href="([^"]+)">(.*?)<svg', h, re.S)
    titles = re.findall(r'<h3 class="proof-grid__title">(.*?)</h3>', h)
    return {**bg(h), 'title': inline(find(r'<h2[^>]*>(.*?)</h2>', h)), 'lead': text(find(r'<p class="section-lead">(.*?)</p>', h)),
            'bigTitle': 'industries__title' in find(r'(<h2[^>]*>)', h), 'sectors': sectors, 'stats': stats, 'resultsTitle': text(titles[0]), 'results': results,
            'linkText': text(link.group(2)), 'linkUrl': url(link.group(1)), 'trustTitle': text(titles[1]), 'trust': trust}


@extractor('latest-articles')
def latest_articles(h):
    b = btn(find(r'(<a class="button[^>]*>.*?</a>)', h))
    return {**bg(h), 'title': inline(find(r'<h2[^>]*>(.*?)</h2>', h)), 'buttonText': b['text'], 'buttonUrl': b['url'], 'count': 3}


@extractor('home-contact')
def home_contact(h):
    ways = [{'icon': icon_of(i), 'label': text(l), 'value': text(v), 'url': url(u)} for i, l, u, v in re.findall(
        r'<li class="contact-way">\s*<span class="icon-tile">(.*?)</span>\s*<div><p class="contact-way__label">(.*?)</p><a class="contact-way__value" href="([^"]+)">(.*?)</a>', h, re.S)]
    cards = [{'style': style, 'bubbles': [text(b) for b in re.findall(r'<p class="contact-human__bubble">(.*?)</p>', c)],
              'image': media(src), 'note': text(note)} for style, c, src, note in re.findall(
        r'<figure class="contact-human__card contact-human__card--([a-z]+)[^"]*"[^>]*>(.*?)<img class="contact-human__person" src="([^"]+)".*?</span>(.*?)</figcaption>', h, re.S)]
    form = find(r'(<form.*?</form>)', h)
    return {**bg(h), 'title': inline(find(r'<h2[^>]*>(.*?)</h2>', h)), 'lead': text(find(r'<p class="section-lead">(.*?)</p>', h)),
            'ways': ways, 'cards': cards, 'formTitle': text(find(r'form-head__title">(.*?)</p>', form)), 'formSub': text(find(r'form-head__sub">(.*?)</p>', form)),
            'services': [text(o) for o in re.findall(r'<option>(.*?)</option>', form)],
            'buttonText': text(find(r'<button[^>]*type="submit"[^>]*>(.*?)<svg', form)),
            'trust': [text(x) for x in re.findall(r'<li>(?:<svg.*?</svg>)?(.*?)</li>', find(r'<ul class="form-trust"[^>]*>(.*?)</ul>', form), re.S)]}



def lis(h, ul_pattern):
    """Plain text of each <li> inside the first list matching ul_pattern (icons dropped)."""
    return [text(re.sub(r'<svg.*?</svg>', '', x, flags=re.S)) for x in re.findall(r'<li[^>]*>(.*?)</li>', find(ul_pattern, h), re.S)]


def section_tag(h):
    return find(r'(<section[^>]*>)', h)


def head(h):
    return {'title': inline(find(r'<h2[^>]*>(.*?)</h2>', h)), 'lead': inline(find(r'<p class="section-lead">(.*?)</p>', h))}


@extractor('page-hero')
def page_hero(h):
    tag = section_tag(h)
    cards = [{'icon': icon_of(i), 'title': text(t), 'url': url(find(r'href="([^"]*)"', t)), 'text': text(x), 'dark': 'bento__card--dark' in c}
             for c, i, t, x in re.findall(r'<li class="(bento__card[^"]*)">\s*<span class="icon-tile[^"]*">(.*?)</span>\s*<h2 class="bento__title">(.*?)</h2>\s*<p>(.*?)</p>', h, re.S)]
    return {'layout': 'center' if 'page-hero--center' in tag else 'split',
            'title': inline(find(r'<h1[^>]*>(.*?)</h1>', h)), 'lead': text(find(r'<p class="page-hero__lead">(.*?)</p>', h)),
            'buttons': [btn(a) for a in re.findall(r'<a [^>]*>.*?</a>', find(r'<div class="hero__actions">(.*?)</div>', h), re.S)],
            'chipsLabel': find(r'<ul class="chip-row"[^>]*aria-label="([^"]*)"', h),
            'chips': [{'label': text(l), 'url': url(u)} for u, l in re.findall(r'<a class="chip" href="([^"]+)">(.*?)</a>', h)],
            'cards': cards}


@extractor('thanks-hero')
def thanks_hero(h):
    title = find(r'<h1[^>]*>(.*?)</h1>', h).replace('<span data-thanks-name></span>', '{name}')
    urgent = find(r'<p class="thanks-hero__urgent">(.*?)</p>', h)
    urgent = re.sub(r'<a href="tel:[^"]*">.*?</a>', '{phone}', urgent)
    urgent = re.sub(r'<a href="mailto:[^"]*">.*?</a>', '{email}', urgent)
    return {'title': inline(title), 'lead': text(find(r'<p class="page-hero__lead">(.*?)</p>', h)),
            'buttons': [btn(a) for a in re.findall(r'<a [^>]*>.*?</a>', find(r'<div class="hero__actions">(.*?)</div>', h), re.S)],
            'urgent': text(urgent)}


@extractor('contact-hero')
def contact_hero(h):
    ways = []
    for li in re.findall(r'<li class="contact-way">(.*?)</li>', h, re.S):
        address = find(r'<address[^>]*>(.*?)</address>', li)
        ways.append({'icon': icon_of(find(r'<span class="icon-tile">(.*?)</span>', li)), 'label': text(find(r'contact-way__label">(.*?)</p>', li)),
                     'value': text(re.sub(r'<br>\s*', '\n', address)) if address else text(find(r'<a class="contact-way__value"[^>]*>(.*?)</a>', li)),
                     'url': '' if address else url(find(r'<a class="contact-way__value" href="([^"]+)"', li))})
    form = find(r'(<form.*?</form>)', h)
    return {'title': inline(find(r'<h1[^>]*>(.*?)</h1>', h)), 'lead': text(find(r'<p class="page-hero__lead">(.*?)</p>', h)),
            'ways': ways, 'notes': lis(h, r'<ul class="note-list"[^>]*>(.*?)</ul>'),
            'formTitle': text(find(r'form-head__title">(.*?)</p>', form)), 'formSub': text(find(r'form-head__sub">(.*?)</p>', form)),
            'services': [text(o) for o in re.findall(r'<option>(.*?)</option>', form)],
            'buttonText': text(find(r'data-submit-label>(.*?)</span>', form)),
            'trust': lis(form, r'<ul class="form-trust"[^>]*>(.*?)</ul>'), 'note': text(find(r'<p class="form-note"><span[^>]*>\*</span>(.*?)</p>', form))}


@extractor('tilt-cards')
def tilt_cards(h):
    items = [{'icon': icon_of(find(r'<span class="icon-tile[^"]*">(.*?)</span>', li)), 'time': text(find(r'<span class="step-time">(.*?)</span>', li)),
              'title': text(find(r'<h3[^>]*>(.*?)</h3>', li)), 'text': text(find(r'<p class="tilt-card__text">(.*?)</p>', li))}
             for li in re.findall(r'<li class="tilt-card">(.*?)</li>', h, re.S)]
    return {**bg(h), **head(h), 'numbered': '<ol class="tilt-grid' in h, 'columns': '3' if 'tilt-grid--3' in h else '', 'items': items}


@extractor('trust-grid')
def trust_grid(h):
    items = [{'icon': icon_of(i), 'title': text(t), 'text': text(x)} for i, t, x in re.findall(
        r'<li class="trust-item">\s*<span class="icon-tile">(.*?)</span>\s*<div><h3 class="trust-item__title">(.*?)</h3><p class="trust-item__text">(.*?)</p>', h, re.S)]
    return {**bg(h), **head(h), 'items': items}


@extractor('quote-strip')
def quote_strip(h):
    people = []
    for fig in re.findall(r'<figure class="quote-card[^"]*".*?</figure>', h, re.S):
        photo = find(r'<img src="([^"]+)"', fig)
        people.append(testimonial(text(find(r'person__name">(.*?)</p>', fig)), text(find(r'person__role">(.*?)</p>', fig)),
                                  text(find(r'<blockquote><p>(.*?)</p>', fig)), media(photo)[6:] if photo else ''))
    head_html = find(r'<div class="section-head[^"]*">(.*?)</div>\s*<div class="quote-strip', h)
    b = btn(find(r'(<a class="button[^>]*>.*?</a>)', head_html)) if 'button' in head_html else {'text': '', 'url': ''}
    cases = [{'tag': text(tag), 'title': text(t), 'text': text(x), 'linkText': text(lt), 'url': url(u)} for tag, t, x, u, lt in re.findall(
        r'<article class="case-card">\s*<span class="card-tag">(.*?)</span>\s*<h3[^>]*>(.*?)</h3>\s*<p class="case-card__text">(.*?)</p>\s*<a class="text-link" href="([^"]+)">(.*?)<svg', h, re.S)]
    return {**bg(h), 'title': inline(find(r'<h2[^>]*>(.*?)</h2>', h)), 'plainAccent': 'accent--plain' in find(r'<h2[^>]*>(.*?)</h2>', h),
            'buttonText': b['text'], 'buttonUrl': b['url'], 'people': people, 'cases': cases}


@extractor('story')
def story(h):
    body = find(r'<div class="story__body">(.*?)</div>', h)
    return {**bg(h), 'title': inline(find(r'<h2[^>]*>(.*?)</h2>', h)),
            'body': '\n\n'.join(inline(p) for p in re.findall(r'<p>(.*?)</p>', body, re.S)),
            'mission': inline(find(r'<p class="mission__text">(.*?)</p>', h))}


@extractor('industry-tags')
def industry_tags(h):
    return {**bg(h), **head(h), 'items': [{'name': text(n), 'filled': 'industry--filled' in c}
                                         for c, n in re.findall(r'<li class="(industry[^"]*)">(.*?)</li>', h)]}


@extractor('talk')
def talk(h):
    tag = section_tag(h)
    looks = [l for l in ('light', 'sand', 'violet', 'plum', 'mark', 'sides') if f'talk--{l}' in tag]
    people = [{'image': media(src), 'size': find(r'talk__person--([a-z]+)', cls)} for cls, src in re.findall(r'<img class="(talk__person[^"]*)" src="([^"?]+)', h)]
    shots = [{'image': media(src)} for src in re.findall(r'<img class="talk__shot[^"]*" src="([^"?]+)', h)]
    actions = find(r'<div class="talk__actions">(.*?)</div>', h)
    notes = re.findall(r'<span class="talk__note talk__note--([a-z]+)">(.*?)</span>', h, re.S)
    return {'background': find(r'section--(cream|white)', tag, default='white'), 'look': looks[0] if looks else '', 'left': 'talk--left' in tag,
            'title': inline(find(r'<h2[^>]*>(.*?)</h2>', h)), 'lead': text(find(r'<p class="talk__lead">(.*?)</p>', h)),
            'buttons': [btn(a) for a in re.findall(r'<a [^>]*>.*?</a>', actions, re.S)], 'contact': 'talk__contact' in h,
            'people': people, 'shots': shots,
            'noteReply': text(dict(notes).get('reply', '')), 'noteSkillsTitle': text(find(r'<b>(.*?)</b>', dict(notes).get('skills', ''))),
            'noteSkills': text(re.sub(r'<b>.*?</b>', '', dict(notes).get('skills', '')))}


@extractor('stats')
def stats(h):
    out = []
    for value, label, note in re.findall(r'<li class="proof-stat">\s*<p class="proof-stat__value">(.*?)</p>\s*<p class="proof-stat__label">(.*?)</p>\s*<p class="proof-stat__note">(.*?)</p>', h, re.S):
        out.append({'number': find(r'data-count="([^"]+)"', value), 'suffix': text(re.sub(r'<span data-count.*?</span>', '', value)), 'label': text(label), 'note': text(note)})
    return {**bg(h), 'label': find(r'<section[^>]*aria-label="([^"]*)"', h), 'stats': out}


@extractor('goals')
def goals(h):
    out = []
    # The prototype leaves each plan's <li> unclosed, so cut from one plan to the next.
    plans = {find(r'id="([^"]+)"', p): p for p in re.split(r'(?=<li class="goal-plan )', h)[1:]}
    for li in re.findall(r'<li class="goal goal--[a-z]+" data-goal>.*?</li>', h, re.S):
        plan_id = find(r'aria-controls="([^"]+)"', li)
        plan = plans.get(plan_id, '')
        acts = re.findall(r'<a class="button[^"]*" href="([^"]+)">(.*?)</a>', find(r'<div class="goal-plan__actions">(.*?)</div>', plan))
        out.append({'color': find(r'goal--([a-z]+)', li), 'key': plan_id.replace('goal-plan-', ''), 'title': text(find(r'<button[^>]*>(.*?)</button>', li)),
                    'text': text(find(r'<p class="goal__text">(.*?)</p>', li)), 'fit': text(find(r'</span> – (.*?)</p>', li)),
                    'steps': [{'when': text(w), 'title': text(t), 'text': text(x)} for w, t, x in re.findall(
                        r'<li class="goal-plan__step"><span class="goal-plan__when">(.*?)</span><strong>(.*?)</strong><span>(.*?)</span></li>', plan, re.S)],
                    'expect': text(find(r'goal-plan__expect-text">(.*?)</p>', plan)),
                    'services': [{'label': text(l), 'url': url(u)} for u, l in re.findall(r'<a href="([^"]+)">(.*?)</a>', find(r'<p class="goal-plan__services">(.*?)</p>', plan))],
                    'primaryText': text(acts[0][1]) if acts else '', 'primaryUrl': url(acts[0][0]) if acts else '',
                    'secondaryText': text(acts[1][1]) if len(acts) > 1 else '', 'secondaryUrl': url(acts[1][0]) if len(acts) > 1 else ''})
    return {**bg(h), **head(h), 'goals': out}


@extractor('service-lines')
def service_lines(h):
    tabs = []
    buttons = re.findall(r'<button class="tabs__tab"[^>]*aria-controls="([^"]+)"[^>]*>.*?tabs__tab-title">(.*?)</span><span class="tabs__tab-sub">(.*?)</span>', h, re.S)
    for key, title, sub in buttons:
        panel = find(r'(<div class="tabs__panel svc-panel" role="tabpanel" id="' + re.escape(key) + r'".*?)(?=<div class="tabs__panel svc-panel"|$)', h)
        slides = []
        for slide in elements(panel, 'div', 'svc-carousel__slide'):
            screens = [inner(x).strip() for x in elements(slide, 'div', 'svc-panel__screen')]
            slides.append({'main': screens[0] if screens else '', 'alt': screens[1] if len(screens) > 1 else ''})
        links = [{'title': text(t), 'description': text(d), 'url': url(u), 'tool': tool} for u, tool, t, d in re.findall(
            r'<a class="svc-link" href="([^"]+)">\s*<span class="svc-link__logo" title="([^"]*)">.*?svc-link__title">(.*?)</span><span class="svc-link__desc">(.*?)</span>', panel, re.S)]
        cta = find(r'<div class="svc-panel__cta">(.*?)</div>', panel)
        c = btn(cta) if cta else {'text': '', 'url': ''}
        tabs.append({'key': key, 'tab': text(title), 'sub': text(sub), 'title': inline(find(r'<h3 class="svc-panel__title">(.*?)</h3>', panel)),
                     'lead': text(find(r'<p class="svc-panel__lead">(.*?)</p>', panel)), 'text': text(find(r'<p class="svc-panel__text">(.*?)</p>', panel)),
                     'slides': slides, 'links': links, 'ctaText': c['text'], 'ctaUrl': c['url']})
    return {**bg(h), 'title': inline(find(r'<h2[^>]*>(.*?)</h2>', h)), 'tabs': tabs}


@extractor('packages')
def packages(h):
    items = []
    for li in re.findall(r'<li class="package[^"]*">.*?</a></li>', h, re.S):
        b = btn(find(r'(<a class="button[^>]*>.*?</a>)', li))
        items.append({'name': text(find(r'package__name">(.*?)</h3>', li)), 'for': text(find(r'package__for">(.*?)</p>', li)),
                      'text': text(find(r'package__text">(.*?)</p>', li)), 'ticks': lis(li, r'<ul class="tick-list[^"]*"[^>]*>(.*?)</ul>'),
                      'featured': 'package--featured' in li, 'badge': text(find(r'package__badge">(.*?)</span>', li)), 'buttonText': b['text'], 'buttonUrl': b['url']})
    return {**bg(h), **head(h), 'items': items, 'note': text(find(r'<p class="packages__note">(.*?)</p>', h))}


@extractor('compare')
def compare(h):
    heads = re.findall(r'<th scope="col"[^>]*>(.*?)</th>', h, re.S)
    def cell(c):
        mark = re.search(r'<span class="compare__(yes|no)" aria-label="([^"]*)">', c)
        if mark:
            default = 'Yes' if mark.group(1) == 'yes' else 'No'
            return mark.group(1) + ('' if mark.group(2) == default else ':' + mark.group(2))
        return text(c)
    rows = [{'label': text(l), 'cells': [cell(c) for c in re.findall(r'<td[^>]*>(.*?)</td>', r, re.S)]}
            for l, r in re.findall(r'<tr><th scope="row">(.*?)</th>(.*?)</tr>', h, re.S)]
    return {**bg(h), **head(h), 'corner': text(heads[0]) if heads else '', 'columns': [text(x) for x in heads[1:]], 'rows': rows,
            'note': text(find(r'<p class="compare__note">(.*?)</p>', h))}


@extractor('tools')
def tools(h):
    return {**bg(h), 'title': inline(find(r'<h2[^>]*>(.*?)</h2>', h)), 'lead': text(find(r'<p class="tools__lead">(.*?)</p>', h)),
            'tools': re.findall(r'<li class="tools__item"[^>]*data-name="([^"]+)"', h)}


@extractor('not-found')
def not_found(h):
    return {'title': inline(find(r'<h1[^>]*>(.*?)</h1>', h)), 'lead': text(find(r'<p class="nf__lead">(.*?)</p>', h)),
            'placeholder': find(r'<input id="nf-search-input"[^>]*placeholder="([^"]*)"', h),
            'sitemap': inline(find(r'<p class="nf__sitemap">(.*?)</p>', h)),
            'buttons': [btn(a) for a in re.findall(r'<a [^>]*>.*?</a>', find(r'<div class="nf__actions">(.*?)</div>', h), re.S)]}


@extractor('link-list')
def link_list(h):
    return {**bg(h), 'title': text(find(r'<h2[^>]*>(.*?)</h2>', h)), 'links': [{'title': text(t), 'description': text(d), 'url': url(u)} for u, t, d in re.findall(
        r'<a class="svc-link" href="([^"]+)">.*?svc-link__title">(.*?)</span><span class="svc-link__desc">(.*?)</span>', h, re.S)]}


_home_industries = EXTRACT['home-industries']
@extractor('home-industries')
def home_industries_any(h):
    """Industries with photos; the numbers, outcomes and trust parts are optional."""
    if 'proof-grid' in h:
        return _home_industries(h)
    return {**bg(h), **head(h), 'sectors': [{'image': media(src), 'name': text(n)} for src, n in re.findall(
        r'<li class="sector".*?<img class="sector__img" src="([^"]+)".*?sector__name">(.*?)</span>', h, re.S)],
        'stats': [], 'resultsTitle': '', 'results': [], 'linkText': '', 'linkUrl': '', 'trustTitle': '', 'trust': []}


_logo_strip = EXTRACT['logo-strip']
@extractor('logo-strip')
def logo_strip_any(h):
    out = _logo_strip(h)
    if not out['clients']:
        first = find(r'<ul class="logo-strip__list[^"]*" role="list">(.*?)</ul>', h)
        for src, alt in re.findall(r'<img src="([^"]+)" alt="([^"]*)"', first):
            SHARED['client'].setdefault(alt, {'name': alt, 'logo': media(src)[6:], 'placeholder': True})
            out['clients'].append(f'post:client:{alt}')
    out['marquee'] = 'logo-strip--marquee' in section_tag(h)
    return out


_home_why = EXTRACT['home-why']
@extractor('home-why')
def home_why_any(h):
    out = _home_why(h)
    if not out['cards']:
        pass
    return out



# ---------------------------------------------------------------- custom sections (one-offs kept as HTML)

def _module_attrs():
    """data-* attributes each JS module looks for → module ID (so a custom section loads only what it uses)."""
    from lib import THEME
    found = {}
    for f in sorted((THEME / 'assets' / 'js' / 'modules').glob('*.js')):
        js = f.read_text(encoding='utf-8')
        for attr in re.findall(r"querySelector(?:All)?\(\s*['`][^'`]*?\[(data-[a-z0-9-]+)", js):
            found.setdefault(attr, set()).add('@intella/' + f.stem)
    return found


MODULE_ATTRS = _module_attrs()
GLOBAL_MODULES = {'@intella/' + m for m in ('topbar', 'navigation', 'mega-menu', 'footer-groups', 'phone-fields', 'form-validation', 'campaign-fields', 'year')}


def code_html(html):
    """HTML for a code field: page links → WordPress addresses; prototype files → Media Library (resolved by the importer)."""
    html = re.sub(r'(src|poster)="(?:\.\./)*(assets/[^"?#]+)[^"]*"', r'\1="{{media:\2}}"', html)
    html = re.sub(r'href="(?:\.\./)*(assets/[^"?#]+)[^"]*"', r'href="{{media:\1}}"', html)
    html = re.sub(r'srcset="[^"]*"', '', html)
    html = re.sub(r'href="([^"#:]+\.html(?:[?#][^"]*)?)"', lambda m: f'href="{url(m.group(1)) if not m.group(1).startswith("?") else m.group(1)}"', html)
    return html.strip()


def custom_section(h, page):
    html = code_html(h)
    mods = sorted({m for attr, ms in MODULE_ATTRS.items() if attr in html for m in ms} - GLOBAL_MODULES)
    label = re.sub(r'\s*\(.*', '', find(r'aria-label="([^"]*)"', find(r'(<section[^>]*>)', h))) or text(find(r'<h[12][^>]*>(.*?)</h[12]>', h))[:60]
    return {'label': label, 'html': html.strip(), 'css': page[:-5], 'modules': mods}


def crumbs_of(h):
    return [{'label': text(li), 'url': url(find(r'href="([^"]+)"', li))}
            for li in re.findall(r'<li[^>]*>(.*?)</li>', find(r'<nav class="breadcrumb"[^>]*>(.*?)</nav>', h), re.S)]


@extractor('hero-short-form')
def hero_short_form(h):
    form = find(r'(<form.*?</form>)', h)
    vis = find(r'<div class="page-hero__visual[^"]*">(.*)</div>\s*</div>\s*</section>', h)
    slides = []
    for s in re.findall(r'<div class="cmhc__slide[^"]*".*?<p class="cmhc__fix">.*?</p>', h, re.S):
        ill = find(r'<div class="cmhc__ill">(.*)</div>\s*<p class="cmhc__fix">', s)
        photo = find(r'<img class="cmhc__ph" src="([^"]+)"', ill)
        slides.append({'kicker': text(find(r'cmhc__lab">(.*?)</p>', s)), 'title': text(find(r'cmhc__title">(.*?)</p>', s)),
                       'card': code_html(re.sub(r'<img class="cmhc__ph"[^>]*>', '', ill)), 'photo': media(photo) if photo else 0,
                       'fix': text(find(r'cmhc__fix"><span>.*?</span>(.*?)</p>', s)),
                       'look': find(r'cmhc__slide--(?!n\d)([\w-]+)', find(r'(<div class="cmhc__slide[^"]*")', s))})
    return {'crumbs': crumbs_of(h), 'title': inline(find(r'<h1[^>]*>(.*?)</h1>', h)), 'lead': text(find(r'<p class="page-hero__lead">(.*?)</p>', h)),
            'buttonText': text(find(r'data-submit-label>(.*?)</span>', form)), 'formLabel': find(r'<form[^>]*aria-label="([^"]*)"', form),
            'service': find(r'name="service" value="([^"]*)"', form), 'notes': lis(h, r'<ul class="note-list[^"]*"[^>]*>(.*?)</ul>'),
            'sliderLabel': find(r'<figure class="cmhc"[^>]*aria-label="([^"]*)"', h),
            'fixLabel': text(find(r'cmhc__fix"><span>(.*?)</span>', h)) or 'How we fix it', 'slides': slides,
            'tools': code_html(find(r'(<ul class="pmtools".*?</ul>)', h)), 'visual': '' if slides else code_html(vis),
            'look': find(r'<section class="page-hero ([^"]*)"', h)}


def head_of(h):
    """Heading (with <em> accent) and intro of a section."""
    return {'title': inline(find(r'<h2[^>]*>(.*?)</h2>', h)), 'lead': text(find(r'<p class="section-lead">(.*?)</p>', h))}


def link_of(h, cls):
    a = find(r'(<a class="' + cls + r'"[^>]*>.*?</a>)', h)
    return {'buttonText': text(re.sub(r'<svg.*?</svg>', '', find(r'<a[^>]*>(.*?)</a>', a), flags=re.S)), 'buttonUrl': url(find(r'href="([^"]*)"', a))}


def figures(h):
    """Rows of a small figures table: <li><span>label</span><b class="… --tone">value</b></li>."""
    return [{'label': text(l), 'value': text(v), 'tone': t} for l, t, v in
            re.findall(r'<li><span>(.*?)</span><b class="pmpf__v pmpf__v--(\w+)">(.*?)</b></li>', h, re.S)]


@extractor('problems')
def problems(h):
    cols = re.findall(r'<span>(.*?)</span>', find(r'<p class="pmpfs__cols"[^>]*>(.*?)</p>', h))
    items = []
    for li in h.split('<li class="pmpf">')[1:]:
        p_side, f_side = find(r'<div class="pmpf__p">(.*?)<span class="pmpf__arrow"', li), find(r'<div class="pmpf__f">(.*)', li)
        before, after = find(r'(<div class="pmpf__ev">.*?</ul>)', p_side), find(r'(<div class="pmpf__ev pmpf__ev--after">.*?</ul>)', f_side)
        items.append({'title': text(find(r'<h3>(.*?)</h3>', p_side)), 'problem': text(find(r'<p>(.*?)</p>', p_side)),
                      'beforeTitle': text(find(r'pmpf__evt">(.*?)</p>', before)), 'before': figures(before),
                      'fix': text(find(r'</span><p>(.*?)</p>', f_side)),
                      'afterTitle': text(find(r'pmpf__evt">(.*?)</p>', after)), 'after': figures(after)})
    return {**bg(h), **head_of(h), 'problemLabel': text(cols[0]) if cols else '', 'fixLabel': text(cols[-1]) if cols else '',
            'items': items, 'visual': code_html(find(r'</div>\s*(<figure class="pmdash".*?</figure>)', h)), 'alt': 'pmprobs--alt' in section_tag(h)}


@extractor('signs')
def signs(h):
    aside = find(r'(<aside.*?</aside>)', h)
    photo = find(r'<div class="signs__photo[^"]*">\s*<img src="([^"]+)"', aside)
    return {**bg(h), **head_of(h), 'items': lis(h, r'<ul class="signs__list"[^>]*>(.*?)</ul>'), 'icon': icon_of(find(r'(<span class="icon-tile.*?</span>)', aside)) or 'check',
            'asideTitle': text(find(r'signs__aside-title">(.*?)</p>', aside)),
            'asideText': text(find(r'signs__aside-title">.*?</p>\s*<p>(.*?)</p>', aside)), **link_of(aside, 'button button--light'),
            'photo': media(photo) if photo else 0, 'photoAlt': find(r'<img[^>]*alt="([^"]*)"', aside) if photo else '',
            'bubble': text(find(r'signs__bubble">(.*?)</p>', aside)), 'shortPhoto': 'signs__photo--short' in aside}


@extractor('sign-strips')
def sign_strips(h):
    items = []
    for li in h.split('<li class="pmst')[1:]:
        body = find(r'<div class="pmst__b"[^>]*><div>(.*)</div></div>', li)
        txt = find(r'<div class="pmst__txt">(.*?)</div>', body) if 'pmst__txt' in body else body
        items.append({'title': text(find(r'<button[^>]*>(.*?)<span class="pmst__x"', li)),
                      'text': '\n\n'.join(text(x) for x in re.findall(r'<p>(.*?)</p>', txt, re.S)),
                      'picture': code_html(find(r'<div class="pmst__pic"[^>]*>(.*)</div></div>$', body))})
    aside = find(r'(<div class="pmsg__aside">.*?</a>)', h)
    return {**bg(h), **head_of(h), 'items': items, 'noteTitle': text(find(r'pmsg__at">(.*?)</p>', aside)),
            'noteText': text(find(r'pmsg__at">.*?</p><p>(.*?)</p>', aside)), **link_of(aside, 'button button--light')}


@extractor('offer')
def offer(h):
    card = find(r'(<div class="fixed__card">.*?</a>)', h)
    return {**bg(h), **head_of(h), 'name': text(find(r'fixed__name">(.*?)</p>', card)), 'price': text(find(r'fixed__price">(.*?)</p>', card)),
            'items': lis(card, r'<ul class="fixed__list"[^>]*>(.*?)</ul>'), 'delivery': text(find(r'fixed__delivery">(.*?)</p>', card)),
            **link_of(card, 'button button--primary button--lg button--block'),
            'visual': code_html(find(r'<div class="fixed__visual">(.*)</div>\s*</div>\s*</div>\s*</section>', h))}


@extractor('case-study')
def case_study(h):
    client = find(r'<p class="bxcase__client">(.*?)</p>', h)
    return {**bg(h), 'look': find(r'<article class="bxcase ?([^"]*)"', h), 'kicker': text(find(r'bxcase__kicker">(.*?)</p>', h)),
            'title': inline(find(r'<h2[^>]*>(.*?)</h2>', h)), 'client': text(find(r'<b>(.*?)</b>', client)),
            'clientInfo': text(re.sub(r'^\s*<b>.*?</b>', '', client)),
            'steps': [{'title': text(t), 'text': text(x)} for t, x in re.findall(r'<li><h3>(.*?)</h3><p>(.*?)</p></li>', find(r'<ol class="bxcase__steps"[^>]*>(.*?)</ol>', h), re.S)],
            **link_of(h, 'button button--light'),
            'stats': [{'value': text(v), 'label': text(l)} for v, l in re.findall(r'<li><b>(.*?)</b><span>(.*?)</span></li>', find(r'<ul class="bxcase__stats"[^>]*>(.*?)</ul>', h), re.S)],
            'chartTitle': text(find(r'<figcaption>(.*?)</figcaption>', h)),
            'bars': [{'height': float(n) if '.' in n else int(n), 'label': text(l)} for n, l in re.findall(r'--h:([\d.]+)%"><span class="visually-hidden">(.*?)</span>', h)],
            'axisStart': text(find(r'bxcase__axis"><span>(.*?)</span>', h)), 'axisEnd': text(find(r'bxcase__axis"><span>.*?</span><span>(.*?)</span>', h))}


@extractor('why-intella')
def why_intella(h):
    quote = find(r'(<figure class="wpwhy__quote">.*?</figure>)', h)
    ref = []
    if quote:
        name, role = text(find(r'<span><b>(.*?)</b>', quote)), text(find(r'</b>(.*?)</span>\s*</figcaption>', quote))
        photo = find(r'<img src="([^"]+)"', quote)
        ref = [testimonial(name, role, text(find(r'<blockquote><p>(.*?)</p>', quote)), media(photo)[6:] if photo else '')]
    return {**bg(h), 'title': inline(find(r'<h2[^>]*>(.*?)</h2>', h)),
            'items': [{'title': text(t), 'text': text(x)} for t, x in re.findall(r'<li><h3>(.*?)</h3><p>(.*?)</p></li>', find(r'<ul class="wpwhy__list"[^>]*>(.*?)</ul>', h), re.S)],
            'facts': [{'value': text(v), 'label': text(l)} for v, l in re.findall(r'<li><b>(.*?)</b><span>(.*?)</span></li>', find(r'<ul class="cmfacts[^"]*"[^>]*>(.*?)</ul>', h), re.S)],
            'bigFacts': 'cmfacts--big' in h, 'quote': ref}


@extractor('testimonials-spread')
def testimonials_spread(h):
    people = []
    for fig in re.findall(r'<figure class="cmvo__panel".*?</figure>', h, re.S):
        name = text(find(r'cmvo__name">(.*?)</p>', fig))
        people.append(testimonial(name, text(find(r'cmvo__role">(.*?)</p>', fig)), text(find(r'cmvo__q"><p>(.*?)</p>', fig)),
                                  media(find(r'<img src="([^"]+)"', fig))[6:] if '<img' in fig else '',
                                  text(find(r'cmvo__proj"><span>Project</span>(.*?)</p>', fig))))
    return {'title': inline(find(r'<h2[^>]*>(.*?)</h2>', h)), 'people': people}


@extractor('related-services')
def related_services(h):
    b = btn(find(r'(<a class="button[^>]*>.*?</a>)', find(r'<div class="section-head[^"]*">(.*?)<ul', h)))
    items = [{'icon': icon_of(i), 'title': text(t), 'url': url(u), 'text': text(x)} for i, u, t, x in re.findall(
        r'<article class="link-card">\s*<span class="icon-tile">(.*?)</span>\s*<h3 class="link-card__title"><a href="([^"]+)">(.*?)</a></h3>\s*<p class="link-card__text">(.*?)</p>', h, re.S)]
    return {**bg(h), 'title': inline(find(r'<h2[^>]*>(.*?)</h2>', h)), 'buttonText': b['text'], 'buttonUrl': b['url'],
            'columns': find(r'link-grid--(\d)', h, default='3'), 'items': items}


@extractor('request-panel')
def request_panel(h):
    form = find(r'(<form.*?</form>)', h)
    return {**bg(h), 'title': inline(find(r'<h2[^>]*>(.*?)</h2>', h)), 'lead': text(find(r'<p class="cta-panel__lead">(.*?)</p>', h)),
            'steps': [{'title': text(t), 'text': text(x)} for t, x in re.findall(r'<li><span class="acta__n">\d+</span><div><b>(.*?)</b><p>(.*?)</p></div></li>', h, re.S)],
            'formTitle': text(find(r'<p class="cta-form__title">(.*?)</p>', form)), 'messageHint': find(r'name="message"[^>]*placeholder="([^"]*)"', form),
            'service': find(r'name="service" value="([^"]*)"', form), 'buttonText': text(find(r'data-submit-label>(.*?)</span>', form)),
            'trust': lis(form, r'<ul class="form-trust"[^>]*>(.*?)</ul>')}


@extractor('service-hero')
def service_hero(h):
    crumbs = [{'label': text(re.sub(r'<[^>]+>', '', li)), 'url': url(find(r'href="([^"]+)"', li))} for li in re.findall(r'<li[^>]*>(.*?)</li>', find(r'<nav class="breadcrumb"[^>]*>(.*?)</nav>', h), re.S)]
    form = find(r'(<form.*?</form>)', h)
    return {'crumbs': crumbs, 'title': inline(find(r'<h1[^>]*>(.*?)</h1>', h)), 'lead': text(find(r'<p class="page-hero__lead">(.*?)</p>', h)),
            'notes': lis(h, r'<ul class="note-list"[^>]*>(.*?)</ul>'), 'formTitle': text(find(r'form-head__title">(.*?)</h2>', form)),
            'formSub': text(find(r'form-head__sub">(.*?)</p>', form)), 'messageHint': find(r'name="message"[^>]*placeholder="([^"]*)"', form),
            'service': find(r'name="service" value="([^"]*)"', form), 'buttonText': text(find(r'<button[^>]*type="submit"[^>]*>(.*?)<svg', form)),
            'trust': lis(form, r'<ul class="form-trust"[^>]*>(.*?)</ul>')}


@extractor('get-in-touch')
def get_in_touch(h):
    people = []
    for fig in re.findall(r'<figure class="quote-slide[^"]*".*?</figure>', h, re.S):
        photo = find(r'<img src="([^"]+)"', fig)
        people.append(testimonial(text(find(r'person__name">(.*?)</p>', fig)), text(find(r'person__role">(.*?)</p>', fig)),
                                  text(find(r'<blockquote><p>(.*?)</p>', fig)), media(photo)[6:] if photo else ''))
    form = find(r'(<form.*?</form>)', h)
    return {**bg(h), 'title': inline(find(r'<h2[^>]*>(.*?)</h2>', h)), 'lead': text(find(r'<p class="cta-panel__lead">(.*?)</p>', h)), 'people': people,
            'formTitle': text(find(r'cta-form__title">(.*?)</p>', form)), 'messageHint': find(r'name="message"[^>]*placeholder="([^"]*)"', form),
            'service': find(r'name="service" value="([^"]*)"', form), 'buttonText': text(find(r'data-submit-label>(.*?)</span>', form)),
            'trust': lis(form, r'<ul class="form-trust"[^>]*>(.*?)</ul>')}


# Prototype section marker → block, for sections that are the same markup as an existing block.
MARKER_BLOCKS = {'FAQ': 'faq', 'Client logos': 'logo-strip', 'Related services': 'related-services',
                 'Testimonials: editorial spread': 'testimonials-spread'}


def block_for(marker, html):
    for key, block in MARKER_BLOCKS.items():
        if marker == key or marker.startswith(key + ' ('):
            return block
    if is_short_hero(marker, html):
        return 'hero-short-form'
    if shape_of(marker, html):
        return shape_of(marker, html)
    if 'talk__panel' in html:
        return 'talk'
    if marker.startswith('Request panel') and 'name="goal"' not in html:
        return 'request-panel'
    if marker.startswith('Page hero with proposal form') and 'name="phone"' in html and 'contact-form__row' in html:
        return 'service-hero'
    if marker.startswith('Get in touch') and 'data-quote-slider' in html:
        return 'get-in-touch'
    return 'custom-section'


# ---------------------------------------------------------------- pages

# page file -> (WordPress title, block name for each top-level <section> in order; None = skip that section)
PAGES = {
    'index.html': ('Home', ['home-hero', 'logo-strip', 'home-services', 'home-audience', 'home-work', 'testimonials',
                            'free-review', 'home-process', 'home-why', 'home-industries', 'latest-articles', 'faq',
                            'home-contact']),
    'thank-you.html': ('Thank you', ['thanks-hero', 'tilt-cards', 'trust-grid', 'logo-strip', 'quote-strip', 'latest-articles']),
    'contact.html': ('Contact', ['contact-hero', 'tilt-cards', 'quote-strip', 'faq']),
    'about.html': ('About us', ['page-hero', 'story', 'tilt-cards', 'home-why', 'industry-tags', 'quote-strip', 'talk']),
    'services.html': ('Services', ['page-hero', 'stats', 'goals', 'service-lines', 'packages', 'compare', 'home-process',
                                   'home-industries', 'tools', 'quote-strip', 'faq', 'talk']),
    '404.html': ('Page not found', ['not-found', 'link-list']),
}

# Service pages: sections map to blocks automatically (block_for); anything else becomes a custom section.
SEO_PAGES = ['seo.html', 'geo.html', 'content-marketing.html', 'local-seo.html', 'ecommerce-seo.html', 'link-building.html',
             'wordpress-seo.html', 'technical-seo.html', 'seo-content-writing.html', 'international-seo.html', 'b2b-seo.html',
             'seo-consulting.html', 'seo-migration.html', 'enterprise-seo.html', 'healthcare-seo.html', 'financial-services-seo.html',
             'real-estate-seo.html', 'white-label-seo.html', 'b2b-content-marketing.html', 'saas-content-marketing.html',
             'fintech-content-marketing.html', 'healthcare-content-marketing.html']
for _p in SEO_PAGES:
    PAGES[_p] = (None, 'auto')

# Performance Marketing group (and the Digital Marketing hub that is its parent page).
PM_PAGES = ['digital-marketing.html', 'performance-marketing.html', 'google-ads.html', 'google-shopping-ads.html', 'youtube-ads.html',
            'linkedin-ads.html', 'meta-ads.html', 'instagram-ads.html', 'tiktok-ads.html', 'chatgpt-ads.html', 'amazon-ads.html',
            'microsoft-ads.html', 'google-ads-conversion-tracking.html', 'meta-conversions-api.html', 'google-ads-suspension.html',
            'merchant-center-suspension.html', 'google-ads-audit.html', 'performance-max.html', 'merchant-center-setup.html',
            'meta-lead-ads.html', 'meta-account-disabled.html', 'linkedin-lead-gen-forms.html', 'tiktok-events-api.html',
            'server-side-tracking.html', 'landing-pages-for-ads.html', 'ad-creative.html', 'google-ads-setup.html',
            'performance-marketing-consulting.html', 'ecommerce-performance-marketing.html', 'b2b-performance-marketing.html']
for _p in PM_PAGES:
    PAGES[_p] = (None, 'auto')


def build(page):
    title, plan = PAGES[page]
    html = read(page)
    if title is None:  # Page title from the H1 (plain text), e.g. "SEO services that bring in enquiries"
        title = re.sub(r'\s*\|.*$', '', text(find(r'<title>(.*?)</title>', html))) or text(find(r'<h1[^>]*>(.*?)</h1>', html))
    found = top_sections(html)
    if plan == 'auto':
        plan = [block_for(m, f) for m, f in found]
    if len(found) != len(plan):
        sys.exit(f'{page}: the plan lists {len(plan)} sections but the page has {len(found)}')
    blocks = []
    for (marker, frag), block in zip(found, plan):
        if block == 'custom-section':
            blocks.append({'name': 'intella/custom-section', 'attrs': custom_section(frag, page)})
        elif block and block in EXTRACT:
            blocks.append({'name': f'intella/{block}', 'attrs': EXTRACT[block](frag)})
        elif block:
            print(f'  (no extractor yet for {block})')
    seo = {'title': text(find(r'<title>(.*?)</title>', html)), 'description': find(r'<meta name="description" content="([^"]*)"', html)}
    out = {'title': title, 'path': url(page if page != 'index.html' else 'index.html'), 'seo': seo, 'blocks': blocks}
    if page == '404.html':  # Shown by 404.php; private so it never appears in menus, search or sitemaps.
        out.update(path='/page-not-found/', status='private')
    if 'noindex' in find(r'<meta name="robots" content="([^"]*)"', html):
        out['noindex'] = True
    (DATA / 'pages').mkdir(exist_ok=True)
    (DATA / 'pages' / (page[:-5] + '.json')).write_text(json.dumps(out, indent=1, ensure_ascii=False), encoding='utf-8')
    print(f'{page}: {len(blocks)} blocks')


if __name__ == '__main__':
    for p in sys.argv[1:] or PAGES:
        build(p)
    shared = {k: list(v.values()) for k, v in SHARED.items()}
    (DATA / 'shared.json').write_text(json.dumps(shared, indent=1, ensure_ascii=False), encoding='utf-8')
    print({k: len(v) for k, v in shared.items()}, 'shared items')
