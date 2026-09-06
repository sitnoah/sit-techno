(() => {
  'use strict';
  const toggle = document.querySelector('.menu-toggle');
  const nav = document.querySelector('#primary-nav');
  const closeMenu = () => { nav?.classList.remove('is-open'); toggle?.setAttribute('aria-expanded', 'false'); };
  toggle?.addEventListener('click', () => { const open = toggle.getAttribute('aria-expanded') !== 'true'; toggle.setAttribute('aria-expanded', String(open)); nav?.classList.toggle('is-open', open); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && nav?.classList.contains('is-open')) { closeMenu(); toggle?.focus(); } });
  nav?.querySelectorAll('a').forEach(a => { if (new URL(a.href).pathname.replace(/\/$/, '') === location.pathname.replace(/\/$/, '')) a.setAttribute('aria-current', 'page'); a.addEventListener('click',closeMenu); });
  matchMedia('(min-width: 901px)').addEventListener('change', e => { if(e.matches) closeMenu(); });
  const form = document.querySelector('#project-form');
  if (!form) return;
  const cfg = window.SIT_CONFIG;
  const live = Boolean(cfg?.endpoint && cfg.enabled);
  const demoNote = document.querySelector('[data-demo-note]');
  if (cfg && !live) demoNote.textContent = 'Online enquiries are not open yet. Please check back soon.';
  else if (live) demoNote.hidden = true;
  const submit = document.querySelector('#submit-project');
  submit.textContent = live ? 'Send enquiry ↗' : cfg ? 'Enquiries not yet open' : 'Preview submission ↗';
  if (cfg && !live) submit.disabled = true;
  const msg = document.querySelector('#form-message');
  const steps = [...form.querySelectorAll('[data-step]')];
  let current = 1, key = crypto.randomUUID(), busy = false;
  const service = new URLSearchParams(location.search).get('service');
  if (service && [...form.elements.service.options].some(o => o.value === service)) form.elements.service.value = service;
  const goal = new URLSearchParams(location.search).get('goal');
  if (goal && ['modernise','build','team','explore'].includes(goal)) form.elements.goal.value = goal;
  function review() {
    const list = document.querySelector('#review-list'); list.replaceChildren();
    const fields = [['goal','Your ambition'],['service','Area of interest'],['brief','Your challenge'],['budget','Budget'],['timeline','Ideal start'],['name','Name'],['email','Email'],['company','Organisation']];
    for (const [name, label] of fields) {
      const input = form.elements[name];
      let value = input.value;
      if (input.tagName === 'SELECT') value = input.selectedOptions[0].textContent;
      if (name === 'goal') value = form.querySelector('input[name="goal"]:checked')?.closest('label').querySelector('span').textContent || '';
      const row=document.createElement('div'), dt=document.createElement('dt'), dd=document.createElement('dd');dt.textContent=label;dd.textContent=value;row.append(dt,dd);list.append(row);
    }
  }
  function show(n) {
    current=n; msg.textContent='';
    steps.forEach(s => { const active=Number(s.dataset.step)===n; s.hidden=!active; s.querySelectorAll('input,select,textarea').forEach(el => el.disabled=!active); });
    form.querySelectorAll('[data-progress]').forEach(p=>{p.classList.toggle('current',Number(p.dataset.progress)===n);if(Number(p.dataset.progress)===n)p.setAttribute('aria-current','step');else p.removeAttribute('aria-current');});
    if(n===3) review();
    const h=steps[n-1].querySelector('h2'); h.tabIndex=-1; h.focus({preventScroll:true});
  }
  function validStep() {
    for (const el of steps[current-1].querySelectorAll('input,select,textarea')) if(!el.checkValidity()){el.reportValidity();return false;}
    return true;
  }
  form.querySelectorAll('[data-next]').forEach(b=>b.addEventListener('click',()=>{if(validStep())show(Math.min(3,current+1));}));
  form.querySelectorAll('[data-back]').forEach(b=>b.addEventListener('click',()=>show(Math.max(1,current-1))));
  form.addEventListener('submit',async e=>{
    e.preventDefault();if(busy)return;
    if(current<3){if(validStep())show(current+1);return;}
    if(cfg&&!live)return;
    steps.forEach(s=>s.querySelectorAll('input,select,textarea').forEach(el=>el.disabled=false));
    for(let i=0;i<steps.length;i++){const bad=[...steps[i].querySelectorAll('input,select,textarea')].find(el=>!el.checkValidity());if(bad){show(i+1);bad.reportValidity();return;}}
    const payload=Object.fromEntries(new FormData(form));
    if(!live){success('This is the design preview. Your sample enquiry was not sent or saved. On the configured WordPress site, this step creates a private enquiry for the SIT team.');return;}
    busy=true;submit.disabled=true;submit.textContent='Sending…';msg.textContent='';
    try {
      const tokenResponse=await fetch(cfg.tokenEndpoint,{credentials:'same-origin',cache:'no-store'});
      if(!tokenResponse.ok)throw new Error('We could not prepare your enquiry. Please try again.');
      const {token}=await tokenResponse.json();
      const response=await fetch(cfg.endpoint,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-SIT-Token':token,'Idempotency-Key':key},body:JSON.stringify(payload)});
      const result=await response.json();
      if(!response.ok)throw new Error(result.message || 'Your enquiry could not be saved. Please try again.');
      success('Thank you. Your enquiry has been saved for the SIT team to review. Your reference is '+result.reference+'.');
    } catch(err){msg.textContent=err.message || 'Connection interrupted. Please try again.';}
    finally {busy=false;submit.disabled=false;submit.textContent='Send enquiry ↗';}
  });
  function success(copy){form.hidden=true;const box=document.querySelector('#form-success');document.querySelector('#success-copy').textContent=copy;box.hidden=false;box.focus({preventScroll:true});}
  document.querySelector('#reset-form').addEventListener('click',()=>{form.reset();key=crypto.randomUUID();document.querySelector('#form-success').hidden=true;form.hidden=false;show(1);});
  // Keep browser constraint validation scoped to the visible step.
  steps.slice(1).forEach(s=>s.querySelectorAll('input,select,textarea').forEach(el=>el.disabled=true));
})();
