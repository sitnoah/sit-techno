const assert=require('node:assert/strict'),fs=require('node:fs'),path=require('node:path');
const {JSDOM}=require(process.env.SIT_TEST_MODULES?path.join(process.env.SIT_TEST_MODULES,'jsdom'):'jsdom');
const root=path.join(__dirname,'..');
const html=fs.readFileSync(path.join(root,'dist/start-a-project/index.html'),'utf8');
const script=fs.readFileSync(path.join(root,'wordpress/themes/sit-technology/assets/site.js'),'utf8');
function create(config){const dom=new JSDOM(html,{url:'https://sit.example/start-a-project/?service=software-engineering',runScripts:'outside-only'});dom.window.matchMedia=()=>({addEventListener(){}});if(config)dom.window.SIT_CONFIG=config;dom.window.eval(script);return dom;}
function fill(w){const f=w.document.querySelector('form');f.elements.goal.value='build';f.elements.brief.value='Build an accessible project coordination tool.';f.querySelector('[data-next]').click();assert.equal(w.document.querySelector('[data-step="2"]').hidden,false);f.elements.name.value='Sample Person';f.elements.email.value='sample@example.com';f.elements.company.value='Example Company';f.elements.consent.checked=true;f.querySelector('[data-step="2"] [data-next]').click();return f;}
(async()=>{
 let dom=create(),w=dom.window,d=w.document,f=d.querySelector('form');
 assert.equal(f.elements.service.value,'software-engineering');
 f.querySelector('[data-next]').click();assert.equal(d.querySelector('[data-step="1"]').hidden,false,'Required goal and brief must block progress');
 f=fill(w);assert.equal(d.querySelector('[data-step="3"]').hidden,false);assert.match(d.querySelector('#review-list').textContent,/Sample Person/);
 f.dispatchEvent(new w.Event('submit',{bubbles:true,cancelable:true}));assert.equal(d.querySelector('#form-success').hidden,false);assert.match(d.querySelector('#success-copy').textContent,/not sent or saved/);
 d.querySelector('#reset-form').click();assert.equal(f.hidden,false);assert.equal(f.elements.email.value,'');assert.equal(d.querySelector('[data-step="1"]').hidden,false);
 d.querySelector('.menu-toggle').click();assert.equal(d.querySelector('.menu-toggle').getAttribute('aria-expanded'),'true');d.dispatchEvent(new w.KeyboardEvent('keydown',{key:'Escape'}));assert.equal(d.querySelector('.menu-toggle').getAttribute('aria-expanded'),'false');dom.window.close();
 dom=create({endpoint:'/wp-json/sit/v1/enquiries',enabled:false});assert.equal(dom.window.document.querySelector('#submit-project').disabled,true);dom.window.close();
 dom=create({endpoint:'/wp-json/sit/v1/enquiries',tokenEndpoint:'/wp-json/sit/v1/enquiry-token',enabled:true});w=dom.window;d=w.document;f=fill(w);let calls=[];
 w.fetch=async(url,options)=>{calls.push([url,options]);return url.includes('token')?{ok:true,json:async()=>({token:'test'})}:{ok:false,json:async()=>({message:'Please try again.'})};};
 f.dispatchEvent(new w.Event('submit',{bubbles:true,cancelable:true}));await new Promise(r=>setImmediate(r));assert.equal(d.querySelector('#form-success').hidden,true);assert.match(d.querySelector('#form-message').textContent,/Please try again/);assert.equal(d.querySelector('#submit-project').disabled,false);
 const key=calls[1][1].headers['Idempotency-Key'];const data=JSON.parse(calls[1][1].body);assert.equal(data.email,'sample@example.com');assert.equal(data.consent,'1');
 w.fetch=async(url,options)=>{calls.push([url,options]);return url.includes('token')?{ok:true,json:async()=>({token:'test'})}:{ok:true,json:async()=>({reference:'SIT-TEST'})};};
 f.dispatchEvent(new w.Event('submit',{bubbles:true,cancelable:true}));await new Promise(r=>setImmediate(r));assert.equal(calls[3][1].headers['Idempotency-Key'],key,'Retry must preserve idempotency key');assert.equal(d.querySelector('#form-success').hidden,false);assert.match(d.querySelector('#success-copy').textContent,/SIT-TEST/);dom.window.close();
 console.log('PASS: form validation, review, demo reset, menu keyboard control, disabled live mode, failed submission and successful idempotent retry.');
})().catch(e=>{console.error(e);process.exitCode=1;});
