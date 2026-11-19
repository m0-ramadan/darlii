/* Rebuild with: docker exec homzen_node node scripts/build-homepage-assets.cjs */
const fs=require('fs'),path=require('path'),crypto=require('crypto'),vm=require('vm');
const {transform}=require('lightningcss');
const postcss=require('postcss');
let coverage;
const publicDir=path.resolve('public'),out=path.join(publicDir,'themes/darlii/performance');
fs.mkdirSync(out,{recursive:true});
const hash=b=>crypto.createHash('sha256').update(b).digest('hex').slice(0,12);
const write=(name,ext,content)=>{const file=`${name}.${hash(content)}.${ext}`;fs.writeFileSync(path.join(out,file),content);return '/themes/darlii/performance/'+file;};
const images={};
for(const [source,name] of [
 ['/themes/darlii/images/darli-hero-cairo-photo-clean.webp','hero-desktop'],
 ['/themes/darlii/images/darli-hero-cairo-photo-clean-900.webp','hero-mobile'],
 ['/themes/darlii/images/logo/darli-logo-ar.png','logo-ar'],
])images[source]=write(name,path.extname(source).slice(1),fs.readFileSync(path.join(publicDir,source)));

const styles=[
'/vendor/fonts/fonts.css',
'/themes/darlii/css/darli-design-system.min.css',
'/vendor/core/plugins/language/css/language-public.css',
'/themes/darlii/plugins/bootstrap/css/bootstrap.rtl.min.css',
'/themes/darlii/css/style.css',
'/vendor/core/plugins/real-estate/css/front-auth.css',
'/vendor/core/plugins/announcement/css/announcement.css',
'/themes/darlii/css/darli-home.min.css',
'/themes/darlii/css/rtl-custom.min.css',
'/themes/darlii/css/locale-switcher.min.css',
'/themes/darlii/css/darli-unified.min.css',
'/themes/darlii/css/darli-seo-a11y.css'];
if(process.argv.includes('--coverage-input')){
 const candidates=[],sources={};
 for(const file of styles){const text=fs.readFileSync(path.join(publicDir,file),'utf8');sources[file]=crypto.createHash('sha256').update(text).digest('hex');postcss.parse(text).walkRules(r=>{const hiding=(r.nodes||[]).some(d=>(d.prop==='display'&&/none/.test(d.value))||(d.prop==='visibility'&&/hidden/.test(d.value)));candidates.push({file,selector:r.selector,hiding,start:r.source.start.offset,end:r.source.end.offset+1});});}
 fs.writeFileSync('resources/performance/selector-candidates.json',JSON.stringify({sources,rules:candidates}));process.exit(0);
}
coverage=JSON.parse(fs.readFileSync('resources/performance/homepage-coverage.json','utf8'));
// Keep the existing cascade order and rebase every relative CSS URL.
let css='',critical='';
for(const file of styles){
 let text=fs.readFileSync(path.join(publicDir,file),'utf8');
 const ranges=coverage[file];
 if(!ranges || ranges.sha256!==crypto.createHash('sha256').update(text).digest('hex')) throw Error('Refresh homepage coverage for '+file);
 const ast=postcss.parse(text);
 const prune=node=>{
  if(node.type==='comment'){node.remove();return;}
  if(node.type==='atrule' && node.nodes && !['font-face','keyframes','-webkit-keyframes'].includes(node.name)){
   [...node.nodes].forEach(prune);if(!node.nodes.length)node.remove();return;
  }
  if(node.type==='rule' && !ranges.ranges.some(r=>r.start<=node.source.end.offset && r.end>node.source.start.offset))node.remove();
 };
 [...ast.nodes].forEach(prune);
 let criticalText=ast.toString();
 const rebase=text=>text.replace(/url\(\s*(["']?)([^)'"\s]+)\1\s*\)/g,(whole,q,url)=>{
  if (/^(?:data:|https?:|\/\/|#)/.test(url)) return whole;
  const absolute=url.startsWith('/')?url:path.posix.normalize(path.posix.join(path.posix.dirname(file),url));
  return `url("${images[absolute]||absolute}")`;
 });
 css+=`\n/* ${file} */\n`+rebase(text);
 critical+=`\n/* ${file} */\n`+rebase(criticalText);
}
const compactFonts=text=>{
 const root=postcss.parse(text),faces=new Map();
 root.walkAtRules('font-face',node=>{
  const family=node.nodes.find(d=>d.prop==='font-family');
  if(!family || !/Cairo/.test(family.value))return;
  const key=node.nodes.filter(d=>d.prop!=='font-weight').map(d=>d.toString()).join(';');
  if(faces.has(key)){node.remove();return;}
  faces.set(key,node);node.nodes.find(d=>d.prop==='font-weight').value='400 900';
 });return root.toString();
};
critical=compactFonts(critical);css=compactFonts(css);
const criticalData=transform({filename:'critical.css',code:Buffer.from(critical),minify:true,errorRecovery:false}).code;
const criticalUrl=write('critical','css',criticalData);
const bundle=transform({filename:'homepage.css',code:Buffer.from(css),minify:true,errorRecovery:false}).code;
const bundleUrl=write('homepage','css',bundle);
const sandbox={};vm.runInNewContext(fs.readFileSync('public/themes/darlii/plugins/lucide/lucide.min.js','utf8'),sandbox);
const names=new Set(JSON.parse(fs.readFileSync('resources/performance/homepage-icon-names.json','utf8')));
function scan(dir){for(const file of fs.readdirSync(dir,{withFileTypes:true})){const p=path.join(dir,file.name);if(file.isDirectory())scan(p);else if(file.name.endsWith('.php')){for(const m of fs.readFileSync(p,'utf8').matchAll(/data-lucide=["']([a-z0-9-]+)["']/g))names.add(m[1]);}}}
scan('resources/views');
scan('app/Services/LegacyFrontend');
const icons={};
for(const name of names){const key=name.split('-').map(x=>x[0].toUpperCase()+x.slice(1)).join('');if(sandbox.lucide.icons[key])icons[name]=sandbox.lucide.icons[key];}
fs.writeFileSync('resources/performance/lucide-icons.json',JSON.stringify(icons));
const manifest={images,styles,bundle:bundleUrl,critical:criticalUrl,icons:Object.keys(icons).length,sourceHash:hash(Buffer.from(css))};
fs.writeFileSync('resources/performance/homepage.json',JSON.stringify(manifest,null,2)+'\n');
console.log(JSON.stringify({bundle:bundleUrl,bytes:bundle.length,criticalBytes:criticalData.length,icons:manifest.icons}));
