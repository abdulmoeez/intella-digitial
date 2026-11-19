"""Split the prototype's section stylesheets into one style.css per block.

A rule goes to a block when every class in its selector appears in that block's HTML (from the prototype pages),
so it is guaranteed to be able to match there. Rules inside @media / @supports keep their wrapper. @keyframes go
with the blocks whose rules use them.

Some rules style only shared classes (e.g. the base .contact-form and .form-card cards, styled again in
components.css), so no single block owns them. Those that the built pages use go to assets/css/sections-shared.css,
loaded on every page after components.css, matching the prototype's cascade. Anything else is reported.

Usage (from bin/import):  python3 css_split.py            (uses BLOCKS from blocks_map.py)
"""
import re, sys
from lib import FRONTEND, THEME

SOURCES = ['sections.css', 'blocks.css', 'mockups.css']
SHARED = 'shared'  # Pseudo-block for rules on shared classes; written to assets/css/sections-shared.css.


def parse(css):
    """Top-level items: ('rule', prelude, body) | ('at', prelude, [items]) | ('keyframes', name, text)."""
    css = re.sub(r'/\*.*?\*/', '', css, flags=re.S)
    items, i, n = [], 0, len(css)
    while i < n:
        j = css.find('{', i)
        if j < 0:
            break
        prelude = css[i:j].strip()
        depth, k = 0, j
        while k < n:
            if css[k] == '{':
                depth += 1
            elif css[k] == '}':
                depth -= 1
                if depth == 0:
                    break
            k += 1
        body = css[j + 1:k]
        if prelude.startswith('@keyframes'):
            items.append(('keyframes', prelude.split()[1], f'{prelude} {{{body}}}'))
        elif prelude.startswith(('@media', '@supports', '@container', '@layer')):
            items.append(('at', prelude, parse(body)))
        elif prelude:
            items.append(('rule', prelude, body))
        i = k + 1
    return items


def classes(prelude):
    return set(re.findall(r'\.(-?[_a-zA-Z][\w-]*)', prelude))


def section_classes(html):
    found = set()
    for attr in re.findall(r'class="([^"]*)"', html):
        found.update(attr.split())
    return found


def assign(items, blocks, used_anim, out, wrapper=None, leftovers=None):
    for kind, prelude, body in items:
        if kind == 'at':
            assign(body, blocks, used_anim, out, prelude if not wrapper else f'{wrapper} and {prelude[6:]}', leftovers)
            continue
        if kind == 'keyframes':
            continue
        need = classes(prelude)
        if not need:
            leftovers.append(prelude)
            continue
        # Split selector lists so each part is judged on its own classes.
        parts = [p.strip() for p in re.split(r',(?![^(]*\))', prelude)]
        claimed = set()
        for name, info in blocks.items():
            if name == SHARED:
                continue
            keep = [p for p in parts if classes(p) and classes(p) <= info['classes'] and classes(p) & info['own']]
            if keep:
                add(out, used_anim, name, wrapper, keep, body)
                claimed.update(keep)
        # Parts no block owns but the built pages use (shared classes only) → the site-wide shared file.
        rest = [p for p in parts if p not in claimed]
        shared = blocks.get(SHARED)
        keep = [p for p in rest if shared and classes(p) and classes(p) <= shared['classes'] and classes(p) & shared['own']]
        if keep:
            add(out, used_anim, SHARED, wrapper, keep, body)
        if not claimed and not keep:
            leftovers.append(prelude)


def add(out, used_anim, name, wrapper, selectors, body):
    out[name].append((wrapper, f"{', '.join(selectors)} {{{body}}}"))
    for anim in re.findall(r'animation(?:-name)?\s*:\s*([\w-]+)', body):
        used_anim.setdefault(name, set()).add(anim)


def js_classes():
    """Classes the JavaScript adds at runtime (classList.add/toggle/remove, className): never in the static HTML."""
    found = set()
    for f in (THEME / 'assets' / 'js').rglob('*.js'):
        js = f.read_text(encoding='utf-8')
        for call in re.findall(r'classList\.(?:add|toggle|remove|contains|replace)\(([^)]*)\)', js):
            found.update(re.findall(r"'([\w-]+)'", call))
        found.update(re.findall(r"'((?:is|has)-[\w-]+)'", js))
    return found


def run(blocks, global_classes):
    """blocks: {block: {'html': [fragments]}}; global_classes: classes styled by global CSS (ignored for ownership)."""
    dynamic = js_classes()
    info = {}
    for name, spec in blocks.items():
        cls = set()
        for frag in spec['html']:
            cls |= section_classes(frag)
        info[name] = {'classes': cls | global_classes | dynamic, 'own': cls - global_classes}
    # Shared: any class the built pages use (including global ones) can match; it must use at least one of them.
    used = set().union(*(i['classes'] - global_classes - dynamic for i in info.values())) | (
        global_classes & set().union(*(section_classes(f) for s in blocks.values() for f in s['html'])))
    info[SHARED] = {'classes': used | global_classes | dynamic, 'own': used}
    out = {name: [] for name in info}
    used_anim, leftovers, keyframes = {}, [], {}
    for src in SOURCES:
        items = parse((FRONTEND / 'assets' / 'css' / src).read_text(encoding='utf-8'))
        for kind, prelude, body in items:
            if kind == 'keyframes':
                keyframes[prelude] = body
        assign(items, info, used_anim, out, None, leftovers)
    for name, rules in out.items():
        css, current = [], None
        for wrapper, rule in rules:
            if wrapper != current:
                if current:
                    css.append('}')
                if wrapper:
                    css.append(f'{wrapper} {{')
                current = wrapper
            css.append(('\t' if wrapper else '') + rule)
        if current:
            css.append('}')
        for anim in sorted(used_anim.get(name, ())):
            if anim in keyframes:
                css.append(keyframes[anim])
        head = (f'/* Section styles on shared classes (base form cards, split headings…), used site-wide. Generated by bin/import/css_split.py. */\n\n'
                if name == SHARED else
                f'/* {name}: styles for this page\'s custom (one-off) sections, split from the prototype CSS by bin/import/css_split.py. */\n\n'
                if name.startswith('custom/') else
                f'/* {name}: section styles, split from the prototype CSS (Code/frontend/assets/css) by bin/import/css_split.py. */\n\n')
        path = (THEME / 'assets' / 'css' / 'sections-shared.css' if name == SHARED else
                THEME / 'assets' / 'css' / 'custom' / f'{name[7:]}.css' if name.startswith('custom/') else
                THEME / 'blocks' / name / 'style.css')
        path.parent.mkdir(parents=True, exist_ok=True)
        path.write_text(head + '\n'.join(css) + '\n', encoding='utf-8')
        print(f'{len(rules):5d} rules  {path.stat().st_size // 1024:4d} KB  {name}')
    return leftovers


def global_classes():
    """Classes styled by the theme's global CSS files (layout, components, utilities…)."""
    cls = set()
    for f in ['base', 'layout', 'mega-menu', 'components', 'utilities']:
        for kind, prelude, body in parse((THEME / 'assets' / 'css' / f'{f}.css').read_text(encoding='utf-8')):
            if kind == 'rule':
                cls |= classes(prelude)
            elif kind == 'at':
                for k2, p2, _ in body:
                    if k2 == 'rule':
                        cls |= classes(p2)
    return cls


def custom_blocks():
    """One pseudo-block per page for its custom sections (from data/pages/*.json), CSS → assets/css/custom/<page>.css."""
    import json
    from lib import DATA
    out = {}
    for f in sorted((DATA / 'pages').glob('*.json')):
        for b in json.loads(f.read_text(encoding='utf-8'))['blocks']:
            if b['name'] == 'intella/custom-section':
                out.setdefault('custom/' + b['attrs']['css'], {'html': []})['html'].append(b['attrs']['html'])
    return out


if __name__ == '__main__':
    from blocks_map import BLOCKS
    left = run({**BLOCKS, **custom_blocks()}, global_classes())
    print(f'\n{len(left)} rules not used by these blocks (they belong to sections not converted yet).')
