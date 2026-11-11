"""Shared helpers for turning the static prototype (Code/frontend) into WordPress content."""
import html as _html, json, pathlib, re

HERE = pathlib.Path(__file__).resolve().parent
FRONTEND = HERE.parents[2] / 'frontend'
THEME = HERE.parents[1] / 'theme' / 'intella'
DATA = HERE / 'data'
URLS = json.loads((DATA / 'urls.json').read_text(encoding='utf-8'))

def read(page):
    """HTML of a prototype page: 'index.html' or a file in pages/."""
    path = FRONTEND / page if page == 'index.html' else FRONTEND / 'pages' / page
    return path.read_text(encoding='utf-8')

def url(href):
    """Prototype link → WordPress path. 'pages/seo.html#x' → '/services/digital-marketing/seo/#x'."""
    if not href or href.startswith(('#', 'http:', 'https:', 'mailto:', 'tel:')):
        return href
    path, _, frag = href.partition('#')
    name = path.split('/')[-1]
    if name in URLS:
        return URLS[name] + ('#' + frag if frag else '')
    return href

def text(s):
    """Plain text from an HTML fragment."""
    return _html.unescape(re.sub(r'<[^>]+>', '', s or '')).strip()

def inline(s):
    """Keep the inline tags blocks allow (accent spans → <em>, strong, br, a); drop the rest."""
    s = s or ''
    s = re.sub(r'<span class="accent[^"]*">(.*?)</span>', r'<em>\1</em>', s, flags=re.S)
    s = re.sub(r'<svg.*?</svg>', '', s, flags=re.S)
    s = re.sub(r'<(?!/?(em|strong|br|a)\b)[^>]+>', '', s)
    s = re.sub(r'href="([^"]*)"', lambda m: f'href="{url(m.group(1))}"', s)
    return _html.unescape(re.sub(r'\s+', ' ', s)).strip()

def section(page_html, name):
    """The HTML between the <!-- ===== name ===== --> marker and the next marker (or </main>)."""
    m = re.search(r'<!-- =+ ' + re.escape(name) + r' =+ -->(.*?)(?=<!-- =+ [^=]+ =+ -->|</main>)', page_html, re.S)
    return m.group(1) if m else ''

def save(name, data):
    (DATA / name).write_text(json.dumps(data, indent=1, ensure_ascii=False), encoding='utf-8')


def top_sections(page_html):
    """Every top-level <section> inside <main>, as (marker name, html). The marker is the
    <!-- ===== Name ===== --> comment just before the section (its first line), or '' if there is none."""
    main = page_html[page_html.find('<main'):page_html.find('</main>')]
    found, depth, start = [], 0, 0
    for m in re.finditer(r'<(/?)section\b[^>]*>', main):
        if not m.group(1):
            if depth == 0:
                start = m.start()
            depth += 1
        else:
            depth -= 1
            if depth == 0:
                before = main[:start]
                c = before.rfind('<!--')
                marker = ''
                if c >= 0 and before[c:].count('-->') == 1 and not before[before.find('-->', c) + 3:].strip():
                    marker = re.sub(r'^=+\s*|\s*=+$', '', before[c + 4:].split('\n')[0].replace('-->', '').strip()).strip()
                found.append((marker, main[start:m.end()]))
    return found


def elements(html, tag, cls):
    """Every complete <tag class="cls…">…</tag> element (nesting-aware), outer HTML."""
    out, pos = [], 0
    opener = re.compile(r'<' + tag + r'\b[^>]*\bclass="' + re.escape(cls) + r'(?:[ "][^"]*)?"[^>]*>')
    tags = re.compile(r'<(/?)' + tag + r'\b[^>]*>')
    while True:
        m = opener.search(html, pos)
        if not m:
            return out
        depth, i = 0, m.start()
        for t in tags.finditer(html, i):
            depth += -1 if t.group(1) else 1
            if depth == 0:
                out.append(html[m.start():t.end()])
                pos = t.end()
                break
        else:
            return out


def inner(element):
    """Inner HTML of an element string."""
    return element[element.find('>') + 1:element.rfind('<')]
