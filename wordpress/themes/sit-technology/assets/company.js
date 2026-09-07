(() => {
  'use strict';
  const menu = document.querySelector('.company-menu');
  if (menu) {
    document.addEventListener('click', event => { if (!menu.contains(event.target)) menu.open = false; });
    menu.addEventListener('focusout', event => { if (event.relatedTarget && !menu.contains(event.relatedTarget)) menu.open = false; });
    menu.addEventListener('keydown', event => {
      if (event.key === 'Escape' && menu.open) { event.preventDefault(); event.stopPropagation(); menu.open = false; menu.querySelector('summary').focus(); }
    });
    menu.querySelectorAll('a').forEach(link => link.addEventListener('click', () => menu.open = false));
  }
  const directory = document.querySelector('[data-team-directory]');
  if (directory) {
    const cards = [...directory.querySelectorAll('[data-role-group]')];
    const search = directory.querySelector('#team-search');
    const buttons = [...directory.querySelectorAll('[data-role-filter]')];
    let category = 'all';
    function filter() {
      const words = search.value.toLocaleLowerCase().trim().split(/\s+/).filter(Boolean);
      let count = 0;
      cards.forEach(card => {
        card.hidden = (category !== 'all' && card.dataset.roleGroup !== category) || !words.every(word => card.dataset.roleSearch.includes(word));
        if (!card.hidden) count++;
      });
      buttons.forEach(button => button.setAttribute('aria-pressed', String(button.dataset.roleFilter === category)));
      directory.querySelector('#team-count').textContent = count + ' proposed role' + (count === 1 ? '' : 's');
      directory.querySelector('.team-empty').hidden = count !== 0;
    }
    search.addEventListener('input', filter);
    buttons.forEach(button => button.addEventListener('click', () => { category = button.dataset.roleFilter; filter(); }));
    directory.querySelector('[data-team-reset]').addEventListener('click', () => { category = 'all'; search.value = ''; filter(); search.focus(); });
  }
  const clocks = [...document.querySelectorAll('[data-office-clock]')];
  function updateClocks() {
    if (document.hidden) return;
    clocks.forEach(clock => {
      const now = new Date();
      try { clock.textContent = new Intl.DateTimeFormat('en-GB', {timeZone:clock.dataset.officeClock,hour:'2-digit',minute:'2-digit',timeZoneName:'short'}).format(now); clock.dateTime = now.toISOString(); }
      catch (_) { clock.textContent = ''; }
    });
  }
  if (clocks.length) { updateClocks(); setInterval(updateClocks, 60000); }
  document.querySelectorAll('[data-copy-address]').forEach(button => button.addEventListener('click', async () => {
    const status = document.querySelector('#location-copy-status');
    try {
      if (!navigator.clipboard?.writeText) throw new Error('Clipboard unavailable');
      await navigator.clipboard.writeText(button.dataset.copyAddress);
      if (status) status.textContent = 'Address copied.';
    } catch (_) { if (status) status.textContent = 'The address could not be copied automatically. Select and copy the published address above.'; }
  }));
  const form = document.querySelector('#contact-form');
  if (!form) return;
  const config = window.SIT_CONTACT_CONFIG;
  const live = Boolean(config?.enabled && config.endpoint && config.tokenEndpoint);
  const fields = form.querySelector('fieldset');
  const submit = form.querySelector('#contact-submit');
  const message = form.querySelector('#contact-status');
  const edit = form.querySelector('#contact-edit');
  const success = document.querySelector('#contact-success');
  const note = document.querySelector('[data-contact-note]');
  let busy = false, pending = null, key = crypto.randomUUID();
  const label = live ? 'Send enquiry ↗' : config ? 'Enquiries not yet open' : 'Preview enquiry ↗';
  submit.textContent = label;
  submit.disabled = Boolean(config && !live);
  if (live) note.hidden = true;
  if (config && !live) { note.textContent = 'Online enquiries are not open yet. Please use any published office contact details below, or check back soon.'; fields.disabled = true; submit.disabled = true; }
  const params = new URLSearchParams(location.search);
  ['region','topic'].forEach(name => {
    const select = form.elements['contact_' + name], value = params.get(name);
    if ([...select.options].some(option => option.value === value)) select.value = value;
  });
  document.querySelectorAll('[data-contact-region]').forEach(link => link.addEventListener('click', event => {
    if (fields.disabled) return;
    const select = form.elements.contact_region;
    if (![...select.options].some(option => option.value === link.dataset.contactRegion)) return;
    event.preventDefault(); select.value = link.dataset.contactRegion; select.focus(); select.scrollIntoView({block:'center',behavior:matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth'});
  }));
  function complete(copy) {
    form.hidden = true; success.hidden = false;
    document.querySelector('#contact-success-copy').textContent = copy;
    success.focus({preventScroll:true});
  }
  form.addEventListener('submit', async event => {
    event.preventDefault();
    if (busy || (config && !live)) return;
    if (!pending) {
      if (!form.reportValidity()) return;
      if (form.elements.brief.value.trim().length < 20) { message.textContent = 'Please add a message of at least 20 characters.'; form.elements.brief.focus(); return; }
      pending = Object.assign(Object.fromEntries(new FormData(form)), {request_type:'enquiry',goal:'explore',service:'explore',budget:'discuss',timeline:'flexible'});
    }
    if (!live) { complete('This is the design preview. Your sample enquiry was not sent or saved.'); return; }
    busy = true; fields.disabled = true; submit.disabled = true; edit.hidden = true;
    form.setAttribute('aria-busy','true'); submit.textContent = 'Sending…'; message.textContent = '';
    try {
      const tokenResponse = await fetch(config.tokenEndpoint, {credentials:'same-origin',cache:'no-store'});
      if (!tokenResponse.ok) throw new Error('We could not prepare your enquiry.');
      const {token} = await tokenResponse.json();
      if (typeof token !== 'string' || !token) throw new Error('We could not prepare your enquiry.');
      const response = await fetch(config.endpoint, {method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-SIT-Token':token,'Idempotency-Key':key},body:JSON.stringify(pending)});
      const result = await response.json();
      if (!response.ok) throw new Error(typeof result.message === 'string' ? result.message : 'We could not confirm your enquiry.');
      if (typeof result.reference !== 'string' || !/^SIT-[A-Z0-9]+$/.test(result.reference)) throw new Error('We could not confirm your reference.');
      complete('Your enquiry has been saved for the SIT team to review. Your reference is ' + result.reference + '. We will agree any meeting or next step with you separately.');
    } catch (error) {
      message.textContent = (error.message || 'The connection was interrupted.') + ' Retry to check the same enquiry safely. Your original details are held for this attempt. If you start a new attempt instead, the previous enquiry may already have been saved.';
      edit.hidden = false;
    } finally { busy = false; submit.disabled = false; form.removeAttribute('aria-busy'); submit.textContent = form.hidden ? label : 'Retry this enquiry ↗'; }
  });
  edit.addEventListener('click', () => {
    if (busy) return;
    pending = null; key = crypto.randomUUID(); fields.disabled = false; edit.hidden = true; submit.textContent = label;
    message.textContent = 'You are starting a new attempt. A previous enquiry may already have been saved.';
    form.elements.name.focus();
  });
  document.querySelector('#contact-reset').addEventListener('click', () => {
    form.reset(); pending = null; key = crypto.randomUUID(); fields.disabled = false; form.hidden = false; success.hidden = true;
    message.textContent = ''; edit.hidden = true; submit.textContent = label; form.elements.name.focus();
  });
})();
