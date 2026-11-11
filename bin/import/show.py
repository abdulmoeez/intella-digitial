"""Print a compact skeleton of prototype sections: python3 show.py page.html [index ...] [--n 1500]"""
import re, sys
from lib import read, top_sections
args = sys.argv[1:]; n = 1500
if '--n' in args: i = args.index('--n'); n = int(args[i + 1]); del args[i:i + 2]
page, idx = args[0], [int(x) for x in args[1:]]
for i, (m, s) in enumerate(top_sections(read(page))):
    if idx and i not in idx: continue
    s = re.sub(r'<svg[^>]*><use href="#i-([a-z-]+)"/></svg>', r'[i:\1]', s)
    s = re.sub(r'<svg.*?</svg>', '[svg]', s, flags=re.S)
    s = re.sub(r'\n\s*\n', '\n', s); s = re.sub(r'\n\t+', '\n', s)
    for tag in ('li', 'article', 'figure', 'details', 'tr', 'button'):
        s = re.sub(r'((?:<%s\b(?:(?!<%s\b).)*?</%s>\s*){2})(?:<%s\b(?:(?!<%s\b).)*?</%s>\s*)+' % ((tag,) * 6), r'\1[…]\n', s, flags=re.S)
    print(f'===== #{i} {m} ({len(s)})\n{s[:n]}')
