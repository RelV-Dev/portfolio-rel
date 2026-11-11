"""Build local SVG masks from the official @fortawesome/fontawesome-free 6.7.2 package.
Usage: python scripts/build-icons.py /path/to/node_modules/@fortawesome/fontawesome-free
"""
import json,re,sys,shutil
from pathlib import Path
from urllib.parse import quote
package=Path(sys.argv[1]); data=json.loads((package/'metadata/icon-families.json').read_text())
files=['api/index.php','dashboard.html','dashboard.js','portfolio-ui.js','data/journey.json']
names=set(re.findall(r'\bfa-([a-z0-9]+(?:-[a-z0-9]+)*)', '\n'.join(Path(f).read_text() for f in files)))
utilities={'solid','regular','brands','spin','2x','3x','fw'}
lookup={name:name for name in data}
for name,info in data.items():
 for alias in info.get('aliases',{}).get('names',[]): lookup[alias]=name
missing=names-utilities-lookup.keys()
if missing: raise SystemExit('Unresolved icons: '+str(sorted(missing)))
css=['/* Font Awesome Free 6.7.2 icons, CC BY 4.0. See LICENSE.txt. SVG masks: no icon font downloads. */',
':is(.fas,.far,.fab,.fa-solid,.fa-regular,.fa-brands){display:inline-block;font-style:normal;line-height:1;}',
':is(.fas,.far,.fab,.fa-solid,.fa-regular,.fa-brands)::before{content:"";display:inline-block;width:var(--icon-width,1em);height:1em;vertical-align:-.125em;background-color:currentColor;-webkit-mask:var(--icon-svg) center/contain no-repeat;mask:var(--icon-svg) center/contain no-repeat;}',
'.fa-spin{animation:icon-spin 2s linear infinite}.fa-2x{font-size:2em}.fa-3x{font-size:3em}.fa-fw{width:1.25em;text-align:center}@keyframes icon-spin{to{transform:rotate(360deg)}}@media(prefers-reduced-motion:reduce){.fa-spin{animation:none}}']
for alias in sorted(names-utilities):
 info=data[lookup[alias]]
 for free in info['familyStylesByLicense']['free']:
  if free['family']!='classic': continue
  style=free['style']; svg=info['svgs']['classic'][style]
  prefix={'solid':'.fas,.fa-solid','regular':'.far,.fa-regular','brands':'.fab,.fa-brands'}[style]
  css.append(':is('+prefix+').fa-'+alias+'{--icon-width:'+str(round(svg['width']/svg['height'],4))+'em;--icon-svg:url("data:image/svg+xml,'+quote(svg['raw'],safe='')+'");}')
Path('assets/icons/icons.css').write_text('\n'.join(css)+'\n')
shutil.copyfile(package/'LICENSE.txt','assets/icons/LICENSE.txt')
print(f'Generated {len(names-utilities)} local icons; no missing names.')
