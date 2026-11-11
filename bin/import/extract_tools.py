"""Tool and platform logos (Google, Semrush, Shopify…) → theme/intella/assets/images/tools/<slug>.svg.
Sections then refer to tools by name and the theme prints the matching logo (intella_tool_logo())."""
import re
from lib import read, THEME, FRONTEND

slug = lambda s: re.sub(r'[^a-z0-9]+', '-', s.lower()).strip('-')
out = THEME / 'assets' / 'images' / 'tools'
out.mkdir(parents=True, exist_ok=True)
found, clashes = {}, []
patterns = [
    r'<span class="svc-link__logo" title="([^"]+)">\s*(<svg.*?</svg>)',
    r'<span class="deck-tool__logo">\s*(<svg.*?</svg>)\s*</span><span class="deck-tool__name">([^<]+)<',
    r'<li class="tools__item"[^>]*data-name="([^"]+)">\s*(<svg.*?</svg>)',
]
pages = ['index.html'] + [p.name for p in (FRONTEND / 'pages').glob('*.html')]
for page in pages:
    h = read(page)
    for i, pat in enumerate(patterns):
        for m in re.finditer(pat, h, re.S):
            name, svg = (m.group(2), m.group(1)) if i == 1 else (m.group(1), m.group(2))
            key = slug(name)
            if key in found and found[key] != svg:
                clashes.append(key)
                continue
            found.setdefault(key, svg)
for key, svg in found.items():
    (out / f'{key}.svg').write_text(svg.strip() + '\n', encoding='utf-8')
print(len(found), 'tool logos;', len(set(clashes)), 'names with more than one drawing (first kept):', sorted(set(clashes))[:20])
