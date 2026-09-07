const assert = require('node:assert/strict'), fs = require('node:fs'), path = require('node:path');
const {JSDOM} = require(path.join(process.env.SIT_TEST_MODULES || '../node_modules', 'jsdom'));
const root = path.join(__dirname,'..');
function page(slug, config, fetch) {
  const dom = new JSDOM(fs.readFileSync(path.join(root,'dist',slug,'index.html'),'utf8'), {url:'https://sit.example/'+slug+'/',runScripts:'outside-only'});
  const w=dom.window;
  w.matchMedia=()=>({matches:false,addEventListener(){}});
  w.HTMLElement.prototype.scrollIntoView=function(){};
  if(config!==undefined) w.SIT_CONTACT_CONFIG=config;
  w.fetch=fetch || (()=>{throw new Error('No network expected')});
  for(const file of ['site','discovery','company']) w.eval(fs.readFileSync(path.join(root,'wordpress/themes/sit-technology/assets',file+'.js'),'utf8'));
  return dom;
}
function fill(w) {
  const f=w.document.querySelector('#contact-form');
  for(const [name,value] of Object.entries({name:'Sample Person',email:'sample@example.com',company:'Example Org',brief:'A sample question about a new software project.',contact_topic:'procurement',contact_region:'liberia'})) f.elements[name].value=value;
  f.elements.consent.checked=true;
  return f;
}
const submit=(w,f)=>f.dispatchEvent(new w.Event('submit',{bubbles:true,cancelable:true}));
const tick=()=>new Promise(resolve=>setImmediate(resolve));
(async()=>{
  let dom=page('team'), w=dom.window,d=w.document;
  const search=d.querySelector('#team-search'), visible=()=>[...d.querySelectorAll('.role-card')].filter(c=>!c.hidden);
  assert.equal(visible().length,20);
  d.querySelector('[data-role-filter="engineering"]').click();assert.equal(visible().length,8);
  search.value='data';search.dispatchEvent(new w.Event('input'));assert.equal(visible().length,3);
  search.value='zzzz';search.dispatchEvent(new w.Event('input'));assert.equal(visible().length,0);assert.equal(d.querySelector('.team-empty').hidden,false);
  d.querySelector('[data-team-reset]').click();assert.equal(visible().length,20);assert.equal(d.activeElement,search);
  const details=visible()[0].querySelector('details');details.querySelector('summary').click();assert.equal(details.open,true);
  const menu=d.querySelector('.company-menu');menu.querySelector('summary').click();assert.equal(menu.open,true);
  menu.querySelector('summary').dispatchEvent(new w.KeyboardEvent('keydown',{key:'Escape',bubbles:true}));assert.equal(menu.open,false);
  d.querySelector('.menu-toggle').click();menu.open=true;
  menu.querySelector('summary').focus();menu.querySelector('summary').dispatchEvent(new w.KeyboardEvent('keydown',{key:'Escape',bubbles:true}));
  assert.equal(d.querySelector('#primary-nav').classList.contains('is-open'),true);assert.equal(menu.open,false);
  d.querySelector('.menu-toggle').click();assert.equal(menu.open,false);dom.window.close();
  dom=page('contact');w=dom.window;d=w.document;let f=fill(w);
  d.querySelector('[data-contact-region="cote-divoire"]').click();assert.equal(f.elements.contact_region.value,'cote-divoire');
  submit(w,f);await tick();assert.equal(f.hidden,true);assert.match(d.querySelector('#contact-success-copy').textContent,/not sent or saved/);
  d.querySelector('#contact-reset').click();assert.equal(f.hidden,false);assert.equal(f.elements.name.value,'');dom.window.close();
  dom=page('contact',{enabled:false});w=dom.window;f=fill(w);submit(w,f);await tick();assert.equal(f.hidden,false);assert.equal(f.querySelector('fieldset').disabled,true);assert.equal(f.querySelector('button[type=submit]').disabled,true);dom.window.close();
  const cfg={enabled:true,endpoint:'/submit',tokenEndpoint:'/token'};
  let calls=[], fail=true, release;
  dom=page('contact',cfg,async(url,opts)=>{
    calls.push({url,opts});
    if(url==='/token') return {ok:true,json:async()=>({token:'valid-token'})};
    if(fail) {await new Promise(resolve=>release=resolve); throw new Error('Connection interrupted');}
    return {ok:true,json:async()=>({reference:'SIT-ABC123'})};
  });w=dom.window;d=w.document;f=fill(w);
  submit(w,f);await tick();submit(w,f);assert.equal(calls.filter(c=>c.url==='/submit').length,1);assert.equal(f.querySelector('fieldset').disabled,true);
  release();await tick();assert.equal(f.hidden,false);assert.equal(d.querySelector('#contact-success').hidden,true);assert.match(d.querySelector('#contact-status').textContent,/same enquiry safely/);
  const first=calls.find(c=>c.url==='/submit');const payload=JSON.parse(first.opts.body);
  assert.equal(payload.contact_region,'liberia');assert.equal(payload.contact_topic,'procurement');assert.equal(payload.consent,'1');assert.equal(payload.request_type,'enquiry');assert.equal(payload.website,'');
  f.elements.brief.value='Attempted change while retry locked';fail=false;submit(w,f);await tick();
  const second=calls.filter(c=>c.url==='/submit')[1];assert.equal(first.opts.headers['Idempotency-Key'],second.opts.headers['Idempotency-Key']);assert.equal(first.opts.body,second.opts.body);
  assert.equal(f.hidden,true);assert.match(d.querySelector('#contact-success-copy').textContent,/SIT-ABC123/);dom.window.close();
  dom=page('contact',cfg,async url=>({ok:true,json:async()=>url==='/token'?{token:'valid-token'}:{reference:'<script>bad</script>'}}));w=dom.window;d=w.document;f=fill(w);submit(w,f);await tick();assert.equal(f.hidden,false);assert.equal(d.querySelector('#contact-success').hidden,true);assert.equal(d.querySelector('#contact-edit').hidden,false);
  d.querySelector('#contact-edit').click();assert.equal(f.querySelector('fieldset').disabled,false);assert.match(d.querySelector('#contact-status').textContent,/previous enquiry may/);dom.window.close();
  console.log('PASS: team search/filter/reset, role disclosure, Company/mobile keyboard handling, office preference, preview/closed/live contact states, duplicate submit protection, immutable retry and malformed confirmation handling.');
})().catch(e=>{console.error(e);process.exitCode=1});
