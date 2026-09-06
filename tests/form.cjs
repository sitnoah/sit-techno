const assert=require('node:assert/strict'),fs=require('node:fs'),path=require('node:path');
const {JSDOM}=require(process.env.SIT_TEST_MODULES?path.join(process.env.SIT_TEST_MODULES,'jsdom'):'jsdom');
const root=path.join(__dirname,'..');
const html=fs.readFileSync(path.join(root,'dist/start-a-project/index.html'),'utf8');
const script=fs.readFileSync(path.join(root,'wordpress/themes/sit-technology/assets/site.js'),'utf8');
function create(config,query='service=software-engineering'){const dom=new JSDOM(html,{url:'https://sit.example/start-a-project/?'+query,runScripts:'outside-only'});dom.window.matchMedia=()=>({addEventListener(){}});if(config)dom.window.SIT_CONFIG=config;dom.window.eval(script);return dom;}
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
 const live={endpoint:'/wp-json/sit/v1/enquiries',tokenEndpoint:'/wp-json/sit/v1/enquiry-token',enabled:true};
 const fields={enquiry:[],consultation:['availability','contact_format','timezone'],'software-project':['product_type','project_stage'],'dedicated-team':['engagement_length','roles','team_size']};
 const common=['request_type','goal','service','brief','budget','timeline','name','email','company','website','consent'];
 for(const type of Object.keys(fields)){
   dom=create(live,'type='+type);w=dom.window;d=w.document;f=d.querySelector('#project-form');
   assert.equal(f.elements.request_type.value,type,'Direct links select the right journey');
   if(type==='consultation'){
     f.elements.brief.value='Discuss a practical technology roadmap with us.';f.querySelector('[data-next]').click();
     assert.equal(d.querySelector('[data-step="1"]').hidden,false,'Consultation requires a time zone');
     f.elements.timezone.value='Europe/London';
   }
   if(type==='dedicated-team'){
     f.elements.brief.value='We need additional engineering capacity for delivery.';f.querySelector('[data-next]').click();
     assert.equal(d.querySelector('[data-step="1"]').hidden,false,'Team journey requires roles');
     f.elements.roles.value='Python and data science';
   }
   f=fill(w);let sent,resolvePost,requests=0;
   w.fetch=async(url,opts)=>{requests++;if(url.includes('token'))return {ok:true,json:async()=>({token:'test'})};sent=JSON.parse(opts.body);return new Promise(resolve=>{resolvePost=resolve;});};
   f.dispatchEvent(new w.Event('submit',{bubbles:true,cancelable:true}));
   f.dispatchEvent(new w.Event('submit',{bubbles:true,cancelable:true}));
   await new Promise(r=>setImmediate(r));
   assert.equal(requests,2,'One token and one POST while sending');
   assert.equal(f.querySelector('[data-step="3"] [data-back]').disabled,true,'No edit during an in-flight request');
   assert.equal(sent.request_type,type);assert.deepEqual(Object.keys(sent).sort(),[...common,...fields[type]].sort(),'Only relevant fields are submitted');
   assert.match(d.querySelector('#review-list').textContent,type==='dedicated-team'?/Python and data science/:type==='consultation'?/Europe\/London/:type==='software-project'?/An idea to explore/:/Start a conversation/);
   resolvePost({ok:true,json:async()=>({reference:'SIT-TEST'})});await new Promise(r=>setImmediate(r));
   assert.equal(d.querySelector('#form-success').hidden,false);
   if(type==='consultation')assert.match(d.querySelector('#success-copy').textContent,/no meeting is booked/);
   assert.equal(w.localStorage.length,0);dom.window.close();
 }
 dom=create(live,'type=dedicated-team');w=dom.window;d=w.document;f=d.querySelector('#project-form');
 f.elements.roles.value='Previous team detail';f.querySelector('[name="request_type"][value="consultation"]').click();f.elements.timezone.value='Africa/Monrovia';
 assert.equal(f.elements.roles.disabled,true);assert.equal(d.querySelector('[data-request-fields="dedicated-team"]').hidden,true);
 f=fill(w);assert.doesNotMatch(d.querySelector('#review-list').textContent,/Previous team detail/);
 let switched;w.fetch=async(url,opts)=>url.includes('token')?{ok:true,json:async()=>({token:'test'})}:(switched=JSON.parse(opts.body),{ok:true,json:async()=>({reference:'SIT-TEST'})});
 f.dispatchEvent(new w.Event('submit',{bubbles:true,cancelable:true}));await new Promise(r=>setImmediate(r));assert.equal('roles' in switched,false);dom.window.close();
 dom=create(undefined,'type=invalid');assert.equal(dom.window.document.querySelector('#project-form').elements.request_type.value,'enquiry');dom.window.close();
 console.log('PASS: all four request journeys, conditional required fields, review, payload isolation, type switching, double-submit protection, idempotent retry, preview/reset and menu controls.');
})().catch(e=>{console.error(e);process.exitCode=1;});
