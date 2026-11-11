"""Map each prototype file (Code/frontend) to its WordPress address, from the wiki's page table.
Writes data/urls.json: {"index.html": "/", "seo.html": "/services/digital-marketing/seo/", ...}.
Pages without an agreed address get a planned one from build/nav_data.py, or /<file-name>/ as a last resort."""
import html, json, pathlib, re, sys

HERE = pathlib.Path(__file__).resolve().parent
FRONTEND = HERE.parents[2] / 'frontend'

def wiki_urls():
    page = (FRONTEND / 'wiki' / 'index.html').read_text(encoding='utf-8')
    found = {}
    for row in re.findall(r'<tr>(.*?)</tr>', page, re.S):
        file = re.search(r'class="file" href="\.\./(?:pages/)?([\w-]+\.html)"', row)
        live = re.search(r'href="https://www\.intelladigital\.com(/[^"]*)"', row)
        if file and live:
            found.setdefault(file.group(1), live.group(1))
    return found

def nav_urls():
    sys.path.insert(0, str(FRONTEND / 'build'))
    from nav_data import CATEGORIES  # noqa: E402
    found = {}
    for cat in CATEGORIES:
        for link in cat['links']:
            if link[2].startswith('/'):
                found.setdefault(link[2], link[2])
    return found

def readme_urls():
    """Addresses from the frontend README table: | Page | `file.html` | `/path/` (or TBC (suggested `/path/`)) | …"""
    found = {}
    for line in (FRONTEND / 'README.md').read_text(encoding='utf-8').splitlines():
        cols = [c.strip() for c in line.split('|')]
        if len(cols) > 3:
            file = re.search(r'`([\w-]+\.html)`', cols[2])
            path = re.search(r'`(/[^`]*)`', cols[3])
            if file and path:
                found.setdefault(file.group(1), path.group(1))
    return found


urls = wiki_urls()
for f, u in readme_urls().items():
    urls.setdefault(f, u)
urls['index.html'] = '/'
planned = nav_urls()
for f in sorted(p.name for p in (FRONTEND / 'pages').glob('*.html')):
    if f in urls:
        continue
    slug = f[:-5]
    guess = next((u for u in planned if u.rstrip('/').endswith('/' + slug)), None)
    urls[f] = guess or f'/{slug}/'
(HERE / 'data' / 'urls.json').write_text(json.dumps(dict(sorted(urls.items())), indent=1), encoding='utf-8')
print(len(urls), 'pages mapped')
