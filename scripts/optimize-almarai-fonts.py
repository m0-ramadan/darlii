"""Losslessly convert the existing Almarai fonts; requires fonttools[woff]."""
from pathlib import Path
from hashlib import sha256
from fontTools.ttLib import TTFont
import json,re
root=Path(__file__).resolve().parents[1]
out=root/'public/themes/darlii/performance/fonts';out.mkdir(parents=True,exist_ok=True)
manifest={}
for weight in [300,400,700,800]:
 source=root/f'public/themes/darlii/fonts/almarai/almarai-{weight}.ttf'
 font=TTFont(source,recalcTimestamp=False);font.flavor='woff2'
 temp=out/f'almarai-{weight}.woff2';font.save(temp)
 data=temp.read_bytes();dest=out/f'almarai-{weight}.{sha256(data).hexdigest()[:12]}.woff2';temp.replace(dest)
 original=TTFont(source);converted=TTFont(dest)
 assert original.getBestCmap()==converted.getBestCmap(), 'Font character map changed'
 manifest[weight]='/themes/darlii/performance/fonts/'+dest.name
 print(weight,source.stat().st_size,'->',len(data))
for name in ['rtl-custom.css','rtl-custom.min.css']:
 path=root/'public/themes/darlii/css'/name;text=path.read_text()
 for weight,url in manifest.items():
  text=re.sub(r'''url\(["']?(?:\.\./fonts/almarai/almarai-'''+str(weight)+r'''\.ttf|/themes/darlii/performance/fonts/almarai-'''+str(weight)+r'''\.[a-f0-9]+\.woff2)["']?\)\s*format\(["'](?:truetype|woff2)["']\)''',lambda m:'url("'+url+'") format("woff2")',text)
 path.write_text(text)
(root/'resources/performance/fonts.json').write_text(json.dumps(manifest,indent=2)+'\n')
