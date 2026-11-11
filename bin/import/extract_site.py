"""Header, mega menu, footer and site settings from the prototype homepage → data/menus.json, data/settings.json.
Also saves the mega menu's brand logos as theme/intella/assets/images/logos/<name>.svg."""
import re
from lib import read, url, text, inline, save, THEME

h = read('index.html')
header = h[h.find('<header'):h.find('</header>')]
footer = h[h.find('<footer'):h.find('</footer>')]
slug = lambda s: re.sub(r'[^a-z0-9]+', '-', s.lower()).strip('-')

# ---- Services mega menu: groups (tabs), their panel links and their offer strips
logos_dir = THEME / 'assets' / 'images' / 'logos'
logos_dir.mkdir(parents=True, exist_ok=True)
saved = {}
groups = []
for gid, tab in re.findall(r'<button class="mega__tab[^"]*"[^>]*id="mega-tab-([a-z]+)"[^>]*>(.*?)</button>', header, re.S):
    panel = re.search(r'<div class="mega__panel[^"]*"[^>]*id="mega-panel-' + gid + r'".*?</ul>', header, re.S).group(0)
    explore = re.search(r'<a class="mega__panel-link" href="([^"]+)">(.*?)<svg', panel, re.S)
    cta = re.search(r'<div class="mega__cta" data-mega-cta="' + gid + r'">(.*?)</div>\s*<a class="[^"]*" href="([^"]+)">(.*?)<svg', header, re.S)
    links = []
    for href, cls, style, logo, title, desc in re.findall(
            r'<li><a class="mega__link mega__link--logo" href="([^"]+)"><span class="(mega__link-logo[^"]*)"(?: style="([^"]*)")? aria-hidden="true">(.*?)</span><span class="mega__link-title">(.*?)</span><span class="mega__link-desc">(.*?)</span></a></li>', panel, re.S):
        icon = re.search(r'#i-([a-z-]+)', logo)
        if icon and 'mega__link-logo--icon' in cls:
            logo_name, variant = icon.group(1), 'icon'
        else:
            if logo not in saved:
                saved[logo] = slug(text(title))
                (logos_dir / f'{saved[logo]}.svg').write_text(logo.strip() + '\n', encoding='utf-8')
            logo_name = saved[logo]
            variant = 'badge' if 'badge' in cls else 'logo'
        links.append(dict(title=text(title), url=url(href), description=text(desc), logo=logo_name, variant=variant, style=style or ''))
    groups.append(dict(
        key=gid,
        title=text(re.search(r'mega__tab-title">(.*?)<', tab).group(1)),
        description=text(re.search(r'mega__tab-sub">(.*?)<', tab).group(1)),
        icon=re.search(r'#i-([a-z-]+)', tab).group(1),
        url=url(explore.group(1)), link_text=text(explore.group(2)),
        cta_title=inline(re.search(r'mega__cta-title">(.*?)</p>', cta.group(1), re.S).group(1)),
        cta_text=text(re.search(r'mega__cta-sub">(.*?)</p>', cta.group(1), re.S).group(1)),
        cta_button=text(cta.group(3)), cta_url=url(cta.group(2)),
        links=links))

general = re.search(r'<div class="mega__cta" data-mega-cta="all">(.*?)</div>\s*<a class="[^"]*" href="([^"]+)">(.*?)<svg', header, re.S)
all_link = re.search(r'<a class="mega__all" href="([^"]+)">(.*?)<svg', header, re.S)

primary = []
for item in re.findall(r'<li class="primary-nav__item[^"]*">(.*?)</li>\s*(?=<li class="primary-nav__item|</ul>\s*<div class="primary-nav__mobile)', header, re.S):
    if 'data-submenu-toggle' in item:
        primary.append(dict(title='Services', url=url(all_link.group(1)), mega=True, link_text=text(all_link.group(2)),
                            cta_title=inline(re.search(r'mega__cta-title">(.*?)</p>', general.group(1), re.S).group(1)),
                            cta_text=text(re.search(r'mega__cta-sub">(.*?)</p>', general.group(1), re.S).group(1)),
                            cta_button=text(general.group(3)), cta_url=url(general.group(2)),
                            children=groups))
    else:
        a = re.search(r'href="([^"]+)">(.*?)</a>', item)
        primary.append(dict(title=text(a.group(2)), url=url(a.group(1))))

def link_list(block):
    return [dict(title=text(t), url=url(u)) for u, t in re.findall(r'<a href="([^"]+)">(.*?)</a>', block)]

company = re.search(r'<nav class="site-footer__company".*?</nav>', footer, re.S).group(0)
resources = re.search(r'<nav class="site-footer__resources".*?</nav>', footer, re.S).group(0)
legal = re.search(r'<ul class="site-footer__legal".*?</ul>', footer, re.S).group(0)
save('menus.json', dict(
    primary=primary,
    footer_company=dict(title='Company', items=link_list(company)),
    footer_resources=dict(title='Resources', items=link_list(resources)),
    legal=dict(title='Legal', items=link_list(legal)),
))

# ---- Site settings (header links from an inner page: the homepage's point at its own sections)
inner = read('seo.html')
top = re.search(r'<span class="topbar__text">(.*?)</span>\s*<a class="topbar__link" href="([^"]+)">(.*?)<svg', inner, re.S)
actions = re.search(r'<div class="site-header__actions">\s*<a class="site-header__contact" href="([^"]+)">(.*?)</a>\s*<a class="[^"]*" href="([^"]+)">(.*?)</a>', inner[inner.find('<header'):inner.find('</header>')], re.S)
fcta = re.search(r'site-footer__cta-title">(.*?)</p>\s*<p class="site-footer__cta-text">(.*?)</p>.*?<a class="[^"]*" href="([^"]+)">(.*?)<svg.*?<a class="[^"]*" href="([^"]+)">(.*?)</a>', footer, re.S)
contact = re.search(r'mailto:([^"]+)".*?tel:[^"]+">(.*?)</a>.*?<span>(.*?)</span>', footer, re.S)
save('settings.json', dict(
    email=contact.group(1), phone=text(contact.group(2)), address=text(contact.group(3)),
    linkedin='', facebook='', instagram='',
    topbar_text=text(top.group(1)), topbar_link_text=text(top.group(3)), topbar_link_url=url(top.group(2)), topbar_version='1',
    header_contact_text=text(actions.group(2)), header_contact_url=url(actions.group(1)),
    header_cta_text=text(actions.group(4)), header_cta_url=url(actions.group(3)),
    footer_cta_title=inline(fcta.group(1)), footer_cta_text=text(fcta.group(2)),
    footer_cta_1_text=text(fcta.group(4)), footer_cta_1_url=url(fcta.group(3)),
    footer_cta_2_text=text(fcta.group(6)), footer_cta_2_url=url(fcta.group(5)),
    footer_about=text(re.search(r'site-footer__about">(.*?)</p>', footer, re.S).group(1)),
))
print(len(groups), 'groups,', sum(len(g['links']) for g in groups), 'links,', len(saved), 'logo files')
