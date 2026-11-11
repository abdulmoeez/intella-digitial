"""Write blocks/<name>/block.json from the compact specs below (fields → editor form + attributes).
Shorthand: ('key', 'type', 'Label', extra-dict). Repeaters: ('key', 'repeater', 'Label', {'itemName':…, 'fields':[…]})."""
import json, sys
from lib import THEME

def f(key, type_, label, **extra):
    d = {'key': key, 'type': type_, 'label': label}
    d.update(extra)
    if type_ in ('repeater', 'list', 'posts') and 'default' not in d:
        d['default'] = []
    return d

BUTTONS = f('buttons', 'repeater', 'Buttons', itemName='button', itemLabel='text', max=3, fields=[
    f('text', 'text', 'Text'), f('url', 'url', 'Link'),
    f('style', 'select', 'Style', options=[{'label': 'Filled (with arrow)', 'value': 'primary'}, {'label': 'Outline', 'value': 'outline'}], default='primary')])

SPECS = {
    'home-hero': dict(title='Homepage hero', icon='cover-image', description='Big heading with buttons, and a rotating stack of service cards with tool logos.',
                      section=None, modules=['@intella/deck'], fields=[
        f('title', 'inline', 'Heading (H1)'), f('lead', 'textarea', 'Intro'), BUTTONS,
        f('cards', 'repeater', 'Service cards', itemName='card', panel='Service cards', fields=[
            f('icon', 'text', 'Icon', help='Sprite icon name, e.g. search, sparkle, megaphone.'), f('title', 'text', 'Title'), f('url', 'url', 'Link'),
            f('text', 'textarea', 'Text'), f('tags', 'list', 'Tags'), f('tools', 'list', 'Tool logos', help='One tool per line (3–5), e.g. Semrush. Logos come from the theme’s tools folder.')])]),
    'logo-strip': dict(title='Client logos', icon='images-alt2', description='A strip of client logos that scrolls in a loop.', section=None, fields=[
        f('label', 'text', 'Label', default='Companies we’ve helped grow'),
        f('clients', 'posts', 'Clients', postType='client', help='Logos come from Clients in the admin menu. Tick the ones to show; none ticked = all.')]),
    'home-services': dict(title='Services by line (tabs)', icon='index-card', description='One tab per service line, each with its services.', section='cream', modules=['@intella/tabs'], fields=[
        f('title', 'inline', 'Heading'), f('lead', 'textarea', 'Intro'), f('buttonText', 'text', 'Button text'), f('buttonUrl', 'url', 'Button link'),
        f('tabs', 'repeater', 'Tabs', itemName='tab', itemLabel='tab', panel='Tabs', fields=[
            f('tab', 'text', 'Tab label'), f('title', 'inline', 'Heading'), f('lead', 'textarea', 'Intro'), f('text', 'textarea', 'Text'),
            f('links', 'repeater', 'Services', itemName='service', fields=[f('title', 'text', 'Name'), f('description', 'text', 'Description'), f('url', 'url', 'Link'), f('tool', 'text', 'Tool logo', help='e.g. Google, Shopify')]),
            f('ctaText', 'text', 'Button text'), f('ctaUrl', 'url', 'Button link')])]),
    'home-audience': dict(title='Problems and fixes (video mosaic)', icon='video-alt3', description='Who we help, with a video mosaic, problem → fix steps and the tools we use.', section='white', modules=['@intella/reels'], fields=[
        f('title', 'inline', 'Heading'), f('lead', 'textarea', 'Intro'), f('buttonText', 'text', 'Button text'), f('buttonUrl', 'url', 'Button link'),
        f('tiles', 'repeater', 'Video tiles (4)', itemName='tile', max=4, panel='Videos', fields=[
            f('clips', 'repeater', 'Clips', itemName='clip', fields=[f('video', 'image', 'Video (MP4)', allowedTypes=['video']), f('poster', 'image', 'Still image')])]),
        f('chips', 'repeater', 'Floating notes (2)', itemName='note', max=2, panel='Videos', fields=[f('title', 'text', 'Title'), f('text', 'text', 'Small text')]),
        f('steps', 'repeater', 'Problems and fixes', itemName='step', itemLabel='problem', panel='Problems and fixes', fields=[f('problem', 'text', 'Problem'), f('answer', 'textarea', 'How we fix it')]),
        f('toolsTitle', 'inline', 'Tools heading', panel='Tools'), f('toolsLead', 'textarea', 'Tools intro', panel='Tools'),
        f('tools', 'list', 'Tools', panel='Tools', help='One tool name per line. Logos come from the theme’s tools folder.')]),
    'home-work': dict(title='Selected work', icon='portfolio', description='Case study finder, work cards and case study cards.', section='cream', modules=['@intella/tabs'], fields=[
        f('title', 'inline', 'Heading'), f('buttonText', 'text', 'Button text'), f('buttonUrl', 'url', 'Button link'),
        f('finderLabel', 'text', 'Finder label', panel='Finder'),
        f('finder', 'repeater', 'Finder tabs', itemName='tab', itemLabel='tab', panel='Finder', fields=[
            f('tab', 'text', 'Tab'), f('chips', 'repeater', 'Links', itemName='link', itemLabel='label', fields=[f('label', 'text', 'Label'), f('url', 'url', 'Link'), f('count', 'text', 'Count')])]),
        f('allText', 'text', '“All case studies” link text', panel='Finder'), f('allUrl', 'url', '“All case studies” link', panel='Finder'),
        f('work', 'repeater', 'Work cards', itemName='card', panel='Work cards', fields=[f('image', 'image', 'Image'), f('alt', 'text', 'Image description'), f('tag', 'text', 'Tag'), f('title', 'text', 'Title'), f('text', 'textarea', 'Text')]),
        f('cases', 'repeater', 'Case study cards', itemName='case study', panel='Case study cards', fields=[f('tag', 'text', 'Tag'), f('title', 'text', 'Title'), f('text', 'textarea', 'Text'), f('linkText', 'text', 'Link text'), f('url', 'url', 'Link')])]),
    'testimonials': dict(title='Testimonials (spotlight)', icon='format-quote', description='Large rotating testimonial with progress bars, plus small quote cards that change in threes.', section='white', modules=['@intella/testimonials'], fields=[
        f('title', 'inline', 'Heading'), f('lead', 'inline', 'Intro'),
        f('spotlight', 'posts', 'Large testimonials', postType='testimonial'), f('cards', 'posts', 'Small quote cards', postType='testimonial', help='Shown three at a time. Tick in order; none ticked = none shown.')]),
    'home-process': dict(title='Process timeline', icon='editor-ol', description='Dark section with a step-by-step timeline that moves on by itself.', section=None, modules=['@intella/journey'], fields=[
        f('title', 'inline', 'Heading'), f('lead', 'textarea', 'Intro'),
        f('getLabel', 'text', '“What you get” label', default='What you get', panel='Steps'), f('needLabel', 'text', '“What we need” label', default='What we need from you', panel='Steps'),
        f('steps', 'repeater', 'Steps', itemName='step', itemLabel='tab', panel='Steps', fields=[
            f('when', 'text', 'When'), f('tab', 'text', 'Short title (timeline)'), f('title', 'text', 'Title (card)'), f('icon', 'text', 'Icon'), f('text', 'textarea', 'Text'),
            f('get', 'list', 'What you get'), f('need', 'list', 'What we need from you')]),
        f('endText', 'text', 'Closing line', panel='Closing'), f('buttonText', 'text', 'Button text', panel='Closing'), f('buttonUrl', 'url', 'Button link', panel='Closing')]),
    'home-why': dict(title='Why Intella (team + cards)', icon='groups', description='Heading, team photo carousel and four short reasons.', section='cream', modules=['@intella/carousels'], fields=[
        f('title', 'inline', 'Heading'), f('lead', 'textarea', 'Intro'), f('buttonText', 'text', 'Button text'), f('buttonUrl', 'url', 'Button link'),
        f('photos', 'repeater', 'Team photos', itemName='photo', itemLabel='alt', panel='Photos', fields=[f('image', 'image', 'Photo (16:9)'), f('alt', 'text', 'Photo description')]),
        f('cards', 'repeater', 'Reasons', itemName='reason', panel='Reasons', fields=[f('icon', 'text', 'Icon'), f('title', 'text', 'Title'), f('text', 'textarea', 'Text'), f('dark', 'toggle', 'Dark card')])]),
    'home-industries': dict(title='Industries and proof', icon='chart-bar', description='Industry photos, headline numbers, rotating outcomes and trust points.', section='white', modules=['@intella/count-up', '@intella/results'], fields=[
        f('title', 'inline', 'Heading'), f('lead', 'textarea', 'Intro'), f('bigTitle', 'toggle', 'Large heading', default=False),
        f('sectors', 'repeater', 'Industries', itemName='industry', itemLabel='name', panel='Industries', fields=[f('image', 'image', 'Photo'), f('name', 'text', 'Name')]),
        f('stats', 'repeater', 'Numbers', itemName='number', itemLabel='label', panel='Numbers', fields=[f('number', 'text', 'Number', help='Counts up from 0, e.g. 150 or 4.9'), f('suffix', 'text', 'After the number', help='e.g. + or /5'), f('label', 'text', 'Label'), f('note', 'text', 'Note')]),
        f('resultsTitle', 'text', 'Outcomes heading', panel='Outcomes'),
        f('results', 'repeater', 'Outcomes', itemName='outcome', itemLabel='value', panel='Outcomes', help='Shown four at a time, shuffling.', fields=[f('icon', 'text', 'Icon'), f('value', 'text', 'Result'), f('label', 'text', 'What improved'), f('who', 'text', 'Client type')]),
        f('linkText', 'text', 'Link text', panel='Outcomes'), f('linkUrl', 'url', 'Link', panel='Outcomes'),
        f('trustTitle', 'text', 'Trust heading', panel='Trust'),
        f('trust', 'repeater', 'Trust points', itemName='point', panel='Trust', fields=[f('icon', 'text', 'Icon'), f('title', 'text', 'Title'), f('text', 'text', 'Text')])]),
    'latest-articles': dict(title='Latest articles', icon='admin-post', description='The newest blog posts as cards (pulled in automatically).', section='cream', fields=[
        f('title', 'inline', 'Heading'), f('buttonText', 'text', 'Button text'), f('buttonUrl', 'url', 'Button link'), f('count', 'number', 'How many', default=3)]),
    'home-contact': dict(title='Contact with form', icon='email', description='Contact options, rotating “how we work” cards and the proposal form.', section='cream', modules=['@intella/human-cards'], fields=[
        f('title', 'inline', 'Heading'), f('lead', 'textarea', 'Intro'),
        f('ways', 'repeater', 'Ways to reach us', itemName='way', itemLabel='label', fields=[f('icon', 'text', 'Icon'), f('label', 'text', 'Label'), f('value', 'text', 'Text'), f('url', 'url', 'Link')]),
        f('cards', 'repeater', '“How we work” cards', itemName='card', itemLabel='note', panel='Cards', fields=[
            f('style', 'select', 'Look', options=['reply', 'plan', 'report'], default='reply'), f('bubbles', 'list', 'Chat bubbles'), f('image', 'image', 'Cut-out photo'), f('note', 'text', 'Note under the photo')]),
        f('formTitle', 'text', 'Form heading', panel='Form'), f('formSub', 'text', 'Form subheading', panel='Form'),
        f('services', 'list', '“What do you need help with?” options', panel='Form'), f('buttonText', 'text', 'Button text', panel='Form'), f('trust', 'list', 'Reassurance points', panel='Form')]),
}

LOOKS = [{'label': l.title() if l else 'Plain', 'value': l} for l in ('', 'light', 'sand', 'violet', 'plum', 'mark', 'sides')]
SPECS.update({
    'page-hero': dict(title='Page hero', icon='cover-image', description='Page title (H1), intro and buttons; centred with jump links, or split with service cards.', section=None, fields=[
        f('layout', 'select', 'Layout', options=[{'label': 'Centred', 'value': 'center'}, {'label': 'Split (cards on the right)', 'value': 'split'}], default='center'),
        f('title', 'inline', 'Heading (H1)'), f('lead', 'textarea', 'Intro'), BUTTONS,
        f('chipsLabel', 'text', 'Jump links label (for screen readers)', panel='Jump links (centred)'),
        f('chips', 'repeater', 'Jump links', itemName='link', itemLabel='label', panel='Jump links (centred)', fields=[f('label', 'text', 'Label'), f('url', 'url', 'Link')]),
        f('cards', 'repeater', 'Cards', itemName='card', panel='Cards (split)', fields=[f('icon', 'text', 'Icon'), f('title', 'text', 'Title'), f('url', 'url', 'Link'), f('text', 'textarea', 'Text'), f('dark', 'toggle', 'Dark card')])]),
    'thanks-hero': dict(title='Thank-you hero', icon='yes-alt', description='Confirmation after a form is sent. {name} is replaced by the visitor’s first name.', section=None, modules=['@intella/thanks-name'], fields=[
        f('title', 'inline', 'Heading (H1)', help='{name} shows the visitor’s first name, e.g. “Thank you{name}.”'), f('lead', 'textarea', 'Intro'), BUTTONS,
        f('urgent', 'text', 'Urgent line', help='{phone} and {email} come from Settings → Intella site.')]),
    'contact-hero': dict(title='Contact hero with form', icon='email-alt', description='Page title, ways to reach us and the enquiry form.', section=None, fields=[
        f('title', 'inline', 'Heading (H1)'), f('lead', 'textarea', 'Intro'),
        f('ways', 'repeater', 'Ways to reach us', itemName='way', itemLabel='label', fields=[f('icon', 'text', 'Icon'), f('label', 'text', 'Label'), f('value', 'text', 'Text'), f('url', 'url', 'Link')]),
        f('notes', 'list', 'Ticked notes'),
        f('formTitle', 'text', 'Form heading', panel='Form'), f('formSub', 'text', 'Form subheading', panel='Form'), f('services', 'list', 'Service options', panel='Form'),
        f('buttonText', 'text', 'Button text', panel='Form'), f('trust', 'list', 'Reassurance points', panel='Form'), f('note', 'text', 'Small print', panel='Form')]),
    'tilt-cards': dict(title='Cards (steps or ideas)', icon='grid-view', description='A row of cards: numbered steps (optionally with a time) or ideas with icons.', section='white', fields=[
        f('title', 'inline', 'Heading'), f('lead', 'inline', 'Intro'),
        f('numbered', 'toggle', 'Numbered steps'), f('columns', 'select', 'Columns', options=[{'label': 'Auto', 'value': ''}, {'label': '3', 'value': '3'}], default=''),
        f('items', 'repeater', 'Cards', itemName='card', fields=[f('icon', 'text', 'Icon (optional)'), f('time', 'text', 'Time label (optional)'), f('title', 'text', 'Title'), f('text', 'textarea', 'Text')])]),
    'trust-grid': dict(title='Trust points', icon='shield', description='Heading and a grid of short trust points with icons.', section='cream', fields=[
        f('title', 'inline', 'Heading'), f('lead', 'inline', 'Intro'),
        f('items', 'repeater', 'Points', itemName='point', fields=[f('icon', 'text', 'Icon'), f('title', 'text', 'Title'), f('text', 'textarea', 'Text')])]),
    'quote-strip': dict(title='Testimonials (two at a time)', icon='format-quote', description='Two testimonials side by side (more rotate in pairs), with optional case study cards.', section='cream', modules=['@intella/quote-pairs'], fields=[
        f('title', 'inline', 'Heading'), f('plainAccent', 'toggle', 'Plain highlight (no gradient)', default=True),
        f('buttonText', 'text', 'Button text (optional)'), f('buttonUrl', 'url', 'Button link'),
        f('people', 'posts', 'Testimonials', postType='testimonial', help='Shown two at a time; with more than two they rotate.'),
        f('cases', 'repeater', 'Case study cards', itemName='case study', panel='Case study cards', fields=[f('tag', 'text', 'Tag'), f('title', 'text', 'Title'), f('text', 'textarea', 'Text'), f('linkText', 'text', 'Link text'), f('url', 'url', 'Link')])]),
    'story': dict(title='Story and mission', icon='book', description='A heading with a few paragraphs, and a mission statement band.', section='white', fields=[
        f('title', 'inline', 'Heading'), f('body', 'textarea', 'Text', rows=10, help='Leave an empty line between paragraphs.'), f('mission', 'inline', 'Mission statement')]),
    'industry-tags': dict(title='Industries (tags)', icon='tag', description='Heading and a list of industries as tags.', section='white', fields=[
        f('title', 'inline', 'Heading'), f('lead', 'inline', 'Intro'),
        f('items', 'repeater', 'Industries', itemName='industry', itemLabel='name', fields=[f('name', 'text', 'Name'), f('filled', 'toggle', 'Highlighted')])]),
    'talk': dict(title='Let’s talk (call to action)', icon='megaphone', description='Closing call to action panel with cut-out people or a photo collage.', section='white', fields=[
        f('look', 'select', 'Look', options=LOOKS, default=''), f('left', 'toggle', 'People on the left'),
        f('title', 'inline', 'Heading'), f('lead', 'textarea', 'Intro'), BUTTONS, f('contact', 'toggle', 'Show phone and email', default=True),
        f('people', 'repeater', 'Cut-out people', itemName='person', panel='Pictures', help='Transparent WebP cut-outs.', fields=[
            f('image', 'image', 'Image'), f('size', 'select', 'Size', options=[{'label': 'Normal', 'value': ''}, {'label': 'Wide', 'value': 'wide'}, {'label': 'Large', 'value': 'l'}], default='')]),
        f('shots', 'repeater', 'Collage photos (instead of people)', itemName='photo', max=2, panel='Pictures', fields=[f('image', 'image', 'Photo')]),
        f('noteReply', 'text', 'Collage note 1', panel='Pictures'), f('noteSkillsTitle', 'text', 'Collage note 2 title', panel='Pictures'), f('noteSkills', 'text', 'Collage note 2 text', panel='Pictures')]),
    'stats': dict(title='Numbers', icon='chart-line', description='A band of headline numbers that count up.', section='cream', modules=['@intella/count-up'], fields=[
        f('label', 'text', 'Label (for screen readers)'),
        f('stats', 'repeater', 'Numbers', itemName='number', itemLabel='label', fields=[f('number', 'text', 'Number'), f('suffix', 'text', 'After the number'), f('label', 'text', 'Label'), f('note', 'text', 'Note')])]),
    'goals': dict(title='Start with your goal', icon='flag', description='Goal cards; each opens a short plan with steps, what to expect and the services used.', section='cream', modules=['@intella/goals'], fields=[
        f('title', 'inline', 'Heading'), f('lead', 'inline', 'Intro'),
        f('goals', 'repeater', 'Goals', itemName='goal', panel='Goals', fields=[
            f('title', 'text', 'Goal'), f('key', 'text', 'Short ID', help='e.g. leads (used in the page address)'),
            f('color', 'select', 'Colour', options=['violet', 'pink', 'teal', 'orange', 'blue', 'amber'], default='violet'),
            f('text', 'textarea', 'Text'), f('fit', 'text', '“We’d use” line'),
            f('steps', 'repeater', 'Plan steps', itemName='step', fields=[f('when', 'text', 'When'), f('title', 'text', 'Title'), f('text', 'textarea', 'Text')]),
            f('expect', 'text', 'What to expect'),
            f('services', 'repeater', 'Services in the plan', itemName='service', itemLabel='label', fields=[f('label', 'text', 'Name'), f('url', 'url', 'Link')]),
            f('primaryText', 'text', 'Main button'), f('primaryUrl', 'url', 'Main button link'), f('secondaryText', 'text', 'Second button'), f('secondaryUrl', 'url', 'Second button link')])]),
    'service-lines': dict(title='All services (side tabs)', icon='list-view', description='One tab per service line with example screens and its services.', section='white', modules=['@intella/tabs', '@intella/carousels'], fields=[
        f('title', 'inline', 'Heading'),
        f('tabs', 'repeater', 'Service lines', itemName='service line', itemLabel='tab', panel='Service lines', fields=[
            f('key', 'text', 'Anchor', help='e.g. seo, so /services/#seo opens this tab'), f('tab', 'text', 'Tab title'), f('sub', 'text', 'Tab subtitle'),
            f('title', 'inline', 'Heading'), f('lead', 'textarea', 'Intro'), f('text', 'textarea', 'Text'),
            f('slides', 'repeater', 'Example screens', itemName='slide', fields=[f('main', 'code', 'Screen'), f('alt', 'code', 'Second screen')]),
            f('links', 'repeater', 'Services', itemName='service', fields=[f('title', 'text', 'Name'), f('description', 'text', 'Description'), f('url', 'url', 'Link'), f('tool', 'text', 'Tool logo')]),
            f('ctaText', 'text', 'Button text'), f('ctaUrl', 'url', 'Button link')])]),
    'packages': dict(title='Packages', icon='money-alt', description='Engagement options side by side; one can be featured.', section='white', fields=[
        f('title', 'inline', 'Heading'), f('lead', 'inline', 'Intro'),
        f('items', 'repeater', 'Packages', itemName='package', itemLabel='name', fields=[
            f('name', 'text', 'Name'), f('for', 'text', 'Best for'), f('text', 'textarea', 'Text'), f('ticks', 'list', 'Ticked points'),
            f('featured', 'toggle', 'Featured'), f('badge', 'text', 'Badge (featured)'), f('buttonText', 'text', 'Button text'), f('buttonUrl', 'url', 'Button link')]),
        f('note', 'text', 'Note under the packages')]),
    'compare': dict(title='Comparison table', icon='editor-table', description='Us vs alternatives. The first column after the labels is highlighted as ours.', section='cream', fields=[
        f('title', 'inline', 'Heading'), f('lead', 'inline', 'Intro'),
        f('corner', 'text', 'Top-left cell (screen readers)', default='What you get'), f('columns', 'list', 'Column headings', help='One per line; the first is us.'),
        f('rows', 'repeater', 'Rows', itemName='row', itemLabel='label', fields=[f('label', 'text', 'Row label'), f('cells', 'list', 'Cells', help='One per line, in column order. Type yes or no for a tick or a dash (yes:Included sets its label).')]),
        f('note', 'text', 'Note under the table')]),
    'tools': dict(title='Tools we use', icon='admin-tools', description='Logo wall of the platforms we use.', section='cream', fields=[
        f('title', 'inline', 'Heading'), f('lead', 'textarea', 'Intro'), f('tools', 'list', 'Tools', help='One tool name per line.')]),
    'not-found': dict(title='Page not found', icon='warning', description='The 404 message with a search of every page.', section=None, modules=['@intella/page-search'], fields=[
        f('title', 'inline', 'Heading (H1)'), f('lead', 'textarea', 'Intro'), f('placeholder', 'text', 'Search box hint'),
        f('sitemap', 'inline', 'Sitemap line', help='May contain a link: Or <a href="/sitemap/">browse every page</a>.'), BUTTONS]),
    'link-list': dict(title='Link list', icon='admin-links', description='A heading and a list of links with short descriptions.', section='white', fields=[
        f('title', 'text', 'Heading'), f('links', 'repeater', 'Links', itemName='link', fields=[f('title', 'text', 'Title'), f('description', 'text', 'Description'), f('url', 'url', 'Link')])]),
})
SPECS['logo-strip']['fields'].append(f('marquee', 'toggle', 'Scroll in a loop', default=True))
for key in ('stats', 'results', 'trust'):
    pass

SPECS['faq'] = dict(title='FAQ', icon='editor-help', description='Questions and answers beside a heading and a contact button. Also adds FAQPage structured data.', section='white', fields=[
    f('title', 'inline', 'Heading', default='Questions, <em>answered</em>'), f('lead', 'textarea', 'Intro', default='Can’t find your answer? Ask us on a free 30-minute call.'),
    f('buttonText', 'text', 'Button text', default='Contact us'), f('buttonUrl', 'url', 'Button link', default='/contact/'),
    f('buttonStyle', 'select', 'Button style', options=[{'label': 'Outline', 'value': 'outline'}, {'label': 'Filled (with arrow)', 'value': 'primary'}], default='outline'),
    f('items', 'repeater', 'Questions', itemName='question', itemLabel='question', fields=[f('question', 'text', 'Question'), f('answer', 'textarea', 'Answer', rows=4)]),
    f('schema', 'toggle', 'Add FAQPage structured data', help='Leave on, unless another FAQ on the same page already adds it.', default=True, panel='Section')])

SPECS.update({
    'related-services': dict(title='Related services', icon='admin-links', description='Cards linking to related services.', section='cream', fields=[
        f('title', 'inline', 'Heading'), f('buttonText', 'text', 'Button text'), f('buttonUrl', 'url', 'Button link'),
        f('columns', 'select', 'Columns', options=['2', '3', '4'], default='3'),
        f('items', 'repeater', 'Services', itemName='service', fields=[f('icon', 'text', 'Icon'), f('title', 'text', 'Name'), f('url', 'url', 'Link'), f('text', 'text', 'Short description')])]),
    'request-panel': dict(title='Request panel (form)', icon='feedback', description='“What happens next” steps beside a short request form. Enquiries arrive under Leads.', section='cream', fields=[
        f('title', 'inline', 'Heading'), f('lead', 'textarea', 'Intro'),
        f('steps', 'repeater', 'What happens next', itemName='step', fields=[f('title', 'text', 'Title'), f('text', 'text', 'Text')]),
        f('formTitle', 'text', 'Form heading', panel='Form'), f('messageHint', 'text', 'Message box hint', panel='Form'),
        f('service', 'text', 'Service (saved with the lead)', panel='Form', help='e.g. wordpress-seo'),
        f('buttonText', 'text', 'Button text', panel='Form'), f('trust', 'list', 'Reassurance points', panel='Form')]),
})
SPECS.update({
    'service-hero': dict(title='Service hero with form', icon='cover-image', description='Breadcrumb, H1, intro and ticked notes beside a short proposal form.', section=None, fields=[
        f('crumbs', 'repeater', 'Breadcrumb', itemName='link', itemLabel='label', help='The last one is the current page (no link needed).', fields=[f('label', 'text', 'Label'), f('url', 'url', 'Link')]),
        f('title', 'inline', 'Heading (H1)'), f('lead', 'textarea', 'Intro'), f('notes', 'list', 'Ticked notes'),
        f('formTitle', 'text', 'Form heading', default='Request a proposal', panel='Form'), f('formSub', 'text', 'Form subheading', panel='Form'),
        f('messageHint', 'text', 'Message box hint', panel='Form'), f('service', 'text', 'Service (saved with the lead)', panel='Form'),
        f('buttonText', 'text', 'Button text', default='Request a proposal', panel='Form'), f('trust', 'list', 'Reassurance points', panel='Form')]),
    'get-in-touch': dict(title='Get in touch (testimonials + form)', icon='email', description='Closing panel: heading and rotating testimonials beside the proposal form.', section='cream', modules=['@intella/quote-sliders'], fields=[
        f('title', 'inline', 'Heading'), f('lead', 'textarea', 'Intro'),
        f('people', 'posts', 'Testimonials', postType='testimonial', help='Rotate one at a time. None ticked = the first two.'),
        f('formTitle', 'text', 'Form heading', default='Request a proposal', panel='Form'), f('messageHint', 'text', 'Message box hint', panel='Form'),
        f('service', 'text', 'Service (saved with the lead)', panel='Form'), f('buttonText', 'text', 'Button text', default='Send message', panel='Form'),
        f('trust', 'list', 'Reassurance points', panel='Form')]),
})
for name, spec in SPECS.items():
    if sys.argv[1:] and name not in sys.argv[1:]:
        continue
    intella = {'fields': spec['fields']}
    if spec.get('section'):
        intella['section'] = spec['section']
    data = {'$schema': 'https://schemas.wp.org/trunk/block.json', 'apiVersion': 3, 'name': f'intella/{name}', 'title': spec['title'],
            'category': 'intella', 'icon': spec['icon'], 'description': spec['description'], 'supports': {'html': False},
            'render': 'file:./render.php', 'style': 'file:./style.css'}
    if spec.get('modules'):
        data['viewScriptModule'] = spec['modules'] if len(spec['modules']) > 1 else spec['modules'][0]
    data['intella'] = intella
    path = THEME / 'blocks' / name / 'block.json'
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(json.dumps(data, indent='\t', ensure_ascii=False) + '\n', encoding='utf-8')
print(len(SPECS), 'block.json files written')
