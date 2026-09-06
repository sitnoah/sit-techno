from pathlib import Path
from datetime import datetime,timezone
import json,re,html,shutil
from urllib.parse import urlsplit
root=Path(__file__).resolve().parents[1];theme=root/'wordpress/themes/sit-technology';out=root/'dist'
source=json.loads((theme/'content/pages.json').read_text())
header=(theme/'content/header.html').read_text();footer=(theme/'content/footer.html').read_text()
def route(s):
 u=urlsplit(s);return '/'+u.path.strip('/')+('/' if u.path else '')+('?' + u.query if u.query else '')+('#' + u.fragment if u.fragment else '')
def resolve(s):
 s=re.sub(r'\[sit_component name="([a-z-]+)"\]',lambda m:(theme/'content/components'/f'{m[1]}.html').read_text(),s)
 s=re.sub(r'\{\{url:(.*?)\}\}',lambda m:route(m[1]),s)
 s=re.sub(r'\{\{asset:(.*?)\}\}',lambda m:'/assets/'+m[1],s)
 return s.replace('{{year}}',str(datetime.now(timezone.utc).year))
def document(title,description,body):
 return '<!doctype html>\n<html lang="en-GB"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>'+html.escape(title)+' | SIT Technology</title><meta name="description" content="'+html.escape(description,quote=True)+'"><meta name="theme-color" content="#f4d63b"><link rel="icon" href="/assets/sit-technology-icon.svg" type="image/svg+xml"><link rel="stylesheet" href="/assets/site.css"><link rel="stylesheet" href="/assets/redesign.css"><script src="/assets/site.js" defer></script><script src="/assets/discovery.js" defer></script></head><body>'+resolve(header)+'<main id="main">'+resolve(body)+'</main>'+resolve(footer)+'</body></html>'
for slug,p in source.items():
 dest=out/slug/'index.html';dest.parent.mkdir(parents=True,exist_ok=True);dest.write_text(document(p['title'],p['description'],p['html']))
(out/'404.html').write_text(document('Page not found','The requested page could not be found.','<section class="wrap error-page"><p class="eyebrow">404 / A DIFFERENT DIRECTION</p><h1>Let’s get you<br>back on track.</h1><p>We couldn’t find that page.</p><a class="button" href="{{url:}}">Back to home ↗</a></section>'))
shutil.copytree(theme/'assets',out/'assets',dirs_exist_ok=True)
(out/'favicon.svg').unlink(missing_ok=True)
(out/'robots.txt').write_text('User-agent: *\nDisallow: /\n')
print(f'Built {len(source)} pages plus a 404; shared theme CSS, JS and asset.')
