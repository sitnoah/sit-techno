from pathlib import Path
from html.parser import HTMLParser
from urllib.parse import urlsplit,unquote
root=Path(__file__).resolve().parents[1]/'dist'
class Document(HTMLParser):
 def __init__(self):super().__init__();self.refs=[];self.ids=[];self.h1=0;self.issues=[]
 def handle_starttag(self,tag,attrs):
  a=dict(attrs)
  if tag=='h1':self.h1+=1
  if 'id' in a:self.ids.append(a['id'])
  if tag in ['a','link','img','script']:
   v=a.get('href') or a.get('src')
   if v:self.refs.append(v)
  if tag=='img' and 'alt' not in a:self.issues.append('Image missing alt')
docs={}
for p in root.rglob('*.html'):
 d=Document();s=p.read_text();d.feed(s);docs[p]=d
 assert '{{' not in s, f'Unresolved marker: {p}'
 assert d.h1==1, f'H1 count {d.h1}: {p}'
 assert len(d.ids)==len(set(d.ids)), f'Duplicate ID: {p}'
 assert not d.issues,(p,d.issues)
for p,d in docs.items():
 for ref in d.refs:
  u=urlsplit(ref)
  if u.scheme or u.netloc:continue
  target=(root/unquote(u.path).lstrip('/')) if u.path.startswith('/') else p.parent/unquote(u.path)
  if target.is_dir():target=target/'index.html'
  assert target.exists(), f'Broken reference {ref} in {p}'
  if u.fragment and target in docs:assert u.fragment in docs[target].ids, f'Broken anchor {ref} in {p}'
print(f'PASS: {len(docs)} HTML pages; internal links, anchors, local assets, unique IDs and heading structure.')
