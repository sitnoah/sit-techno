from pathlib import Path
from zipfile import ZipFile,ZIP_DEFLATED
import argparse,hashlib
root=Path(__file__).resolve().parents[1]
p=argparse.ArgumentParser();p.add_argument('destination',type=Path);args=p.parse_args();args.destination.mkdir(parents=True,exist_ok=True)
items=[('themes','sit-technology','sit-technology-theme-0.4.3.zip','style.css'),('plugins','sit-technology-core','sit-technology-core-0.3.1.zip','sit-technology-core.php')]
for kind,slug,name,entry in items:
 source=root/'wordpress'/kind/slug;target=args.destination/name
 with ZipFile(target,'w',ZIP_DEFLATED,compresslevel=9) as z:
  for f in sorted(source.rglob('*')):
   if f.is_file():z.write(f,Path(slug)/f.relative_to(source))
 with ZipFile(target) as z:
  assert z.testzip() is None
  assert f'{slug}/{entry}' in z.namelist()
  assert all(n.startswith(slug+'/') and '..' not in Path(n).parts for n in z.namelist())
 print(f'{target} | {target.stat().st_size} bytes | SHA256 {hashlib.sha256(target.read_bytes()).hexdigest()}')
