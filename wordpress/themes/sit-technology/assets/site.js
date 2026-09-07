(() => {
  'use strict';
  const toggle = document.querySelector('.menu-toggle');
  const nav = document.querySelector('#primary-nav');
  const background = new Set();
  function setMenu(open) {
    if (!nav || !toggle) return;
    nav.classList.toggle('is-open', open);
    toggle.setAttribute('aria-expanded', String(open));
    if (toggle.firstChild?.nodeType === 3) toggle.firstChild.textContent = open ? 'Close ' : 'Menu ';
    const icon = toggle.querySelector('[aria-hidden="true"]');
    if (icon) icon.textContent = open ? '×' : '☰';
    document.body.classList.toggle('sit-menu-open', open);
    if (open) {
      document.querySelectorAll('#main,.site-footer,.utility-bar,.brand,.skip-link,#wpadminbar').forEach(element => {
        if (!element.hasAttribute('inert')) { background.add(element); element.setAttribute('inert', ''); }
      });
    } else {
      background.forEach(element => element.removeAttribute('inert'));
      background.clear();
      const submenu = nav.querySelector('#expertise-menu');
      if (submenu) submenu.hidden = true;
      nav.querySelector('.expertise-toggle')?.setAttribute('aria-expanded', 'false');
    }
  }
  const closeMenu = () => setMenu(false);
  toggle?.addEventListener('click', () => setMenu(toggle.getAttribute('aria-expanded') !== 'true'));
  document.addEventListener('sit:close-menu', closeMenu);
  document.addEventListener('keydown', e => {
    if (!nav?.classList.contains('is-open')) return;
    if (e.key === 'Escape') { e.preventDefault(); closeMenu(); toggle?.focus(); }
    if (e.key === 'Tab') {
      const controls = [toggle, ...nav.querySelectorAll('a[href],button,input,select,textarea,[tabindex]')]
        .filter(element => element && !element.disabled && element.tabIndex >= 0 && !element.closest('[hidden],[inert]'));
      const first = controls[0], last = controls[controls.length - 1];
      if (e.shiftKey && (document.activeElement === first || !controls.includes(document.activeElement))) {
        e.preventDefault(); last?.focus();
      } else if (!e.shiftKey && (document.activeElement === last || !controls.includes(document.activeElement))) {
        e.preventDefault(); first?.focus();
      }
    }
  });
  nav?.querySelectorAll('a').forEach(a => { if (new URL(a.href).pathname.replace(/\/$/, '') === location.pathname.replace(/\/$/, '')) a.setAttribute('aria-current', 'page'); a.addEventListener('click',closeMenu); });
  matchMedia('(min-width: 901px)').addEventListener('change', e => { if(e.matches) closeMenu(); });
  const form = document.querySelector('#project-form');
  if (!form) return;
  const cfg = window.SIT_CONFIG;
  const live = Boolean(cfg?.endpoint && cfg.enabled);
  const demoNote = document.querySelector('[data-demo-note]');
  if (cfg && !live) demoNote.textContent = 'Online requests are not open yet. Please check back soon.';
  else if (live) demoNote.hidden = true;
  const submit = document.querySelector('#submit-project');
  const submitLabel = live ? 'Send request ↗' : cfg ? 'Requests not yet open' : 'Preview submission ↗';
  submit.textContent = submitLabel;
  const msg = document.querySelector('#form-message');
  const steps = [...form.querySelectorAll('[data-step]')];
  const specific = [...form.querySelectorAll('[data-request-fields]')];
  let current = 1, key = crypto.randomUUID(), busy = false;
  const params = new URLSearchParams(location.search);
  const types = ['enquiry','consultation','software-project','dedicated-team'];
  const service = params.get('service'), goal = params.get('goal'), requestedType = params.get('type');
  if (service && [...form.elements.service.options].some(o => o.value === service)) form.elements.service.value = service;
  if (goal && ['modernise','build','team','explore'].includes(goal)) form.elements.goal.value = goal;
  const initialType = types.includes(requestedType) ? requestedType : (goal === 'team' || service === 'dedicated-teams') ? 'dedicated-team' : (goal === 'build' || goal === 'modernise' || service === 'software-engineering') ? 'software-project' : 'enquiry';
  form.elements.request_type.value = initialType;
  if (!goal && initialType === 'software-project') form.elements.goal.value = 'build';
  if (!goal && initialType === 'dedicated-team') form.elements.goal.value = 'team';
  function syncControls(allSteps = false) {
    const type = form.elements.request_type.value;
    specific.forEach(group => group.hidden = group.dataset.requestFields !== type);
    steps.forEach(step => step.querySelectorAll('input,select,textarea').forEach(el => {
      const group = el.closest('[data-request-fields]');
      el.disabled = busy || (!allSteps && Number(step.dataset.step) !== current) || Boolean(group && group.dataset.requestFields !== type);
    }));
    form.querySelectorAll('button').forEach(button => button.disabled = busy || (button === submit && Boolean(cfg && !live)));
  }
  form.querySelectorAll('[name="request_type"]').forEach(radio => radio.addEventListener('change', () => {
    const type = form.elements.request_type.value;
    form.elements.goal.value = type === 'software-project' ? 'build' : type === 'dedicated-team' ? 'team' : 'explore';
    syncControls();
  }));
  function review() {
    const list = document.querySelector('#review-list'); list.replaceChildren();
    const fields = [['request_type','Request'],['goal','Your ambition'],['service','Area of interest']];
    const activeGroup = specific.find(group => group.dataset.requestFields === form.elements.request_type.value);
    activeGroup?.querySelectorAll('input,select,textarea').forEach(input => fields.push([input.name, activeGroup.querySelector('label[for="' + input.id + '"]').textContent]));
    fields.push(['brief','Your challenge']);
    form.querySelectorAll('[data-brief-context]').forEach(input => { if(input.value.trim()) fields.push([input.name,form.querySelector('label[for="'+input.id+'"]').textContent]); });
    fields.push(['budget','Budget'],['timeline','Ideal start'],['name','Name'],['email','Email'],['company','Organisation']);
    for (const [name, label] of fields) {
      const input = form.elements[name];
      let value = input.value || 'Not specified';
      if (input.tagName === 'SELECT') value = input.selectedOptions[0].textContent;
      if (name === 'request_type') value = form.querySelector('input[name="request_type"]:checked')?.closest('label').querySelector('strong').textContent || '';
      const row = document.createElement('div'), dt = document.createElement('dt'), dd = document.createElement('dd');
      dt.textContent = label; dd.textContent = value; row.append(dt,dd); list.append(row);
    }
  }
  function show(n) {
    current = n; msg.textContent = '';
    steps.forEach(s => s.hidden = Number(s.dataset.step) !== n);
    syncControls();
    form.querySelectorAll('[data-progress]').forEach(p => {
      p.classList.toggle('current', Number(p.dataset.progress) === n);
      if (Number(p.dataset.progress) === n) p.setAttribute('aria-current','step'); else p.removeAttribute('aria-current');
    });
    if (n === 3) review();
    const h = steps[n-1].querySelector('h2'); h.tabIndex = -1; h.focus({preventScroll:true});
  }
  function validStep() {
    for (const el of steps[current-1].querySelectorAll('input,select,textarea')) {
      if (!el.disabled && !el.checkValidity()) { el.reportValidity(); return false; }
    }
    return true;
  }
  form.querySelectorAll('[data-next]').forEach(b => b.addEventListener('click', () => { if (!busy && validStep()) show(Math.min(3,current+1)); }));
  form.querySelectorAll('[data-back]').forEach(b => b.addEventListener('click', () => { if (!busy) show(Math.max(1,current-1)); }));
  form.querySelectorAll('[data-edit-step]').forEach(button => button.addEventListener('click', () => { if(!busy) show(Number(button.dataset.editStep)); }));
  document.querySelector('#download-brief')?.addEventListener('click', () => {
    if(busy || current !== 3) return;
    const content = 'SIT Consultancy — project brief draft\nNot submitted by this download.\n\n' + [...document.querySelectorAll('#review-list > div')].map(row => row.querySelector('dt').textContent + '\n' + row.querySelector('dd').textContent).join('\n\n');
    const file = new Blob([content], {type:'text/plain;charset=utf-8'});
    const link = document.createElement('a'), fileURL = URL.createObjectURL(file);
    link.href = fileURL; link.download = 'sit-consultancy-project-brief.txt'; document.body.append(link); link.click(); link.remove();
    setTimeout(() => URL.revokeObjectURL(fileURL), 1000);
    msg.textContent = 'Your brief download has been prepared. The request has not been submitted.';
  });
  form.addEventListener('submit', async e => {
    e.preventDefault(); if (busy) return;
    if (current < 3) { if (validStep()) show(current+1); return; }
    if (cfg && !live) return;
    syncControls(true);
    for (let i=0; i<steps.length; i++) {
      const bad = [...steps[i].querySelectorAll('input,select,textarea')].find(el => !el.disabled && !el.checkValidity());
      if (bad) { show(i+1); bad.reportValidity(); return; }
    }
    const payload = Object.fromEntries(new FormData(form));
    form.querySelectorAll('[data-brief-context]').forEach(input => { if(!payload[input.name]?.trim()) delete payload[input.name]; });
    if (!live) { success('This is the design preview. Your sample request was not sent or saved. On the configured WordPress site, it creates a private request for the SIT team.'); return; }
    busy = true; syncControls(); submit.textContent = 'Sending…'; msg.textContent = ''; form.setAttribute('aria-busy','true');
    try {
      const tokenResponse = await fetch(cfg.tokenEndpoint, {credentials:'same-origin',cache:'no-store'});
      if (!tokenResponse.ok) throw new Error('We could not prepare your request. Please try again.');
      const {token} = await tokenResponse.json();
      const response = await fetch(cfg.endpoint, {method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-SIT-Token':token,'Idempotency-Key':key},body:JSON.stringify(payload)});
      const result = await response.json();
      if (!response.ok) throw new Error(result.message || 'Your request could not be saved. Please try again.');
      if (typeof result.reference !== 'string' || !/^SIT-[A-Z0-9]+$/.test(result.reference)) throw new Error('We could not confirm your reference. Please retry this submission.');
      success('Thank you. Your request has been saved for the SIT team to review. Your reference is ' + result.reference + '.' + (payload.request_type === 'consultation' ? ' We’ll agree a suitable time with you; no meeting is booked yet.' : ''));
    } catch (err) { msg.textContent = err.message || 'Connection interrupted. Please try again.'; }
    finally { busy = false; form.removeAttribute('aria-busy'); syncControls(); submit.textContent = submitLabel; }
  });
  function success(copy) {
    form.hidden = true;
    const box = document.querySelector('#form-success');
    document.querySelector('#success-copy').textContent = copy; box.hidden = false; box.focus({preventScroll:true});
  }
  document.querySelector('#reset-form').addEventListener('click', () => {
    form.reset(); key = crypto.randomUUID(); document.querySelector('#form-success').hidden = true; form.hidden = false; show(1);
  });
  syncControls();
})();
