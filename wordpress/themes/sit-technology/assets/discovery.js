(() => {
  'use strict';
  const expertise = document.querySelector('.nav-expertise');
  const trigger = expertise?.querySelector('.expertise-toggle');
  const menu = document.querySelector('#expertise-menu');
  function closeExpertise(returnFocus = false) {
    if (!trigger || !menu) return;
    const wasOpen = !menu.hidden;
    menu.hidden = true; trigger.setAttribute('aria-expanded', 'false');
    if (returnFocus && wasOpen) trigger.focus();
  }
  trigger?.addEventListener('click', () => { const open = menu.hidden; menu.hidden = !open; trigger.setAttribute('aria-expanded', String(open)); });
  document.addEventListener('click', e => { if (!expertise?.contains(e.target)) closeExpertise(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeExpertise(true); });
  expertise?.addEventListener('focusout', () => setTimeout(() => { if (!expertise.contains(document.activeElement)) closeExpertise(); }, 0));
  menu?.querySelectorAll('a').forEach(link => link.addEventListener('click', () => closeExpertise()));
  document.querySelector('.menu-toggle')?.addEventListener('click', () => { if (document.querySelector('.menu-toggle').getAttribute('aria-expanded') === 'false') closeExpertise(); });

  document.querySelectorAll('[data-tabs]').forEach(group => {
    const tabs = [...group.querySelectorAll('[role="tab"]')];
    const panels = tabs.map(t => document.getElementById(t.getAttribute('aria-controls')));
    const industry = group.classList.contains('industry-explorer');
    function select(index, focus = false, updateHash = false) {
      if (index < 0 || index >= tabs.length) return;
      tabs.forEach((tab, i) => { tab.setAttribute('aria-selected', String(i === index)); tab.tabIndex = i === index ? 0 : -1; panels[i].hidden = i !== index; });
      if (focus) tabs[index].focus();
      if (industry && updateHash) history.replaceState(null, '', '#' + index);
    }
    tabs.forEach((tab, i) => {
      tab.addEventListener('click', () => select(i, false, true));
      tab.addEventListener('keydown', e => {
        let next;
        if (['ArrowRight', 'ArrowDown'].includes(e.key)) next = (i + 1) % tabs.length;
        if (['ArrowLeft', 'ArrowUp'].includes(e.key)) next = (i - 1 + tabs.length) % tabs.length;
        if (e.key === 'Home') next = 0;
        if (e.key === 'End') next = tabs.length - 1;
        if (next !== undefined) { e.preventDefault(); select(next, true, true); }
      });
    });
    if (industry) {
      const fromHash = () => { const match = location.hash.match(/^#([0-7])$/); if (match) select(Number(match[1])); };
      fromHash(); window.addEventListener('hashchange', fromHash);
    }
  });

  const finder = document.querySelector('[data-service-finder]');
  if (finder) {
    const input = finder.querySelector('#service-search');
    const buttons = [...finder.querySelectorAll('[data-filter]')];
    const cards = [...finder.querySelectorAll('[data-category]')];
    let category = 'all';
    function filter() {
      const words = input.value.toLocaleLowerCase().trim().split(/\s+/).filter(Boolean);
      let count = 0;
      cards.forEach(card => { const match = (category === 'all' || card.dataset.category === category) && words.every(w => card.dataset.search.includes(w)); card.hidden = !match; if (match) count++; });
      finder.querySelector('#service-count').textContent = count + (count === 1 ? ' service to explore' : ' services to explore');
      finder.querySelector('.finder-empty').hidden = count > 0;
      buttons.forEach(button => button.setAttribute('aria-pressed', String(button.dataset.filter === category)));
    }
    buttons.forEach(button => button.addEventListener('click', () => { category = button.dataset.filter; filter(); }));
    input.addEventListener('input', filter);
    finder.querySelector('[data-reset-finder]').addEventListener('click', () => { category = 'all'; input.value = ''; filter(); input.focus(); });
    input.value = new URLSearchParams(location.search).get('q') || ''; filter();
  }

  const search = document.querySelector('#site-search');
  if (search) {
    let catalog = [], previousFocus;
    try { catalog = window.SIT_SEARCH_PAGES || JSON.parse(document.querySelector('#sit-search-data')?.textContent || '[]'); } catch (_) { catalog = []; }
    const input = search.querySelector('#site-search-input');
    const results = search.querySelector('#search-results');
    const status = search.querySelector('#search-status');
    function renderSearch() {
      const query = input.value.toLocaleLowerCase().trim();
      const words = query.split(/\s+/).filter(Boolean);
      const matches = catalog.filter(item => words.every(w => `${item.title} ${item.description} ${item.category}`.toLocaleLowerCase().includes(w)))
        .sort((a, b) => Number(b.title.toLocaleLowerCase().includes(query)) - Number(a.title.toLocaleLowerCase().includes(query)));
      results.replaceChildren();
      status.textContent = query ? `${matches.length} ${matches.length === 1 ? 'result' : 'results'}` : 'Explore popular pages';
      matches.slice(0, query ? 12 : 6).forEach(item => {
        const target = new URL(item.url, location.href);
        if (target.origin !== location.origin) return;
        const link = document.createElement('a'); link.className = 'search-result'; link.href = target.href;
        const category = document.createElement('span'), title = document.createElement('h3'), copy = document.createElement('p');
        category.textContent = item.category; title.textContent = item.title; copy.textContent = item.description;
        link.append(category, title, copy); results.append(link);
      });
      if (!matches.length) {
        const empty = document.createElement('div'); empty.className = 'search-empty';
        const heading = document.createElement('h3'), copy = document.createElement('p');
        heading.textContent = 'Let’s try a different phrase.'; copy.textContent = 'Search for AI, software, cloud, teams or delivery.'; empty.append(heading, copy); results.append(empty);
      }
    }
    function closeSearch() {
      if (typeof search.close === 'function') search.close(); else search.removeAttribute('open');
      previousFocus?.focus();
    }
    document.querySelectorAll('.search-open').forEach(button => button.addEventListener('click', () => {
      previousFocus = button.closest('.primary-nav.is-open') ? document.querySelector('.menu-toggle') : button;
      document.dispatchEvent(new Event('sit:close-menu'));
      closeExpertise();
      if (typeof search.showModal === 'function') search.showModal(); else search.setAttribute('open', '');
      renderSearch(); input.focus();
    }));
    search.querySelector('.search-close').addEventListener('click', closeSearch);
    search.addEventListener('cancel', e => { e.preventDefault(); closeSearch(); });
    search.addEventListener('click', e => { if (e.target === search) { const r = search.getBoundingClientRect(); if (e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom) closeSearch(); } });
    search.addEventListener('keydown', e => {
      if (e.key === 'Escape') { e.preventDefault(); closeSearch(); }
      if (e.key === 'Tab') {
        const focusable = [...search.querySelectorAll('button,input,a[href]')].filter(el => !el.disabled && !el.hidden);
        const first = focusable[0], last = focusable[focusable.length - 1];
        if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
        if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
      }
    });
    search.querySelectorAll('[data-search-term]').forEach(button => button.addEventListener('click', () => { input.value = button.dataset.searchTerm; renderSearch(); input.focus(); }));
    input.addEventListener('input', renderSearch);
  }

  const planner = document.querySelector('#ai-planner');
  if (planner) {
    const result = document.querySelector('#ai-result');
    const side = document.querySelector('.planner-side');
    const recommendations = {
      purpose: ['Define one decision or task to improve and who it serves.', 'Agree a measurable outcome and a small set of acceptance criteria.'],
      data: ['Identify the required information, its owner and permitted uses.', 'Check data quality, access permissions and representative examples.'],
      owner: ['Choose an accountable business sponsor and delivery owner.', 'Confirm who will make decisions and review the pilot.'],
      controls: ['Identify sensitive data, likely failure modes and required human review.', 'Document approved tools, review responsibilities and operating limits.'],
      delivery: ['Map how the proposed tool would fit into a real workflow.', 'Agree a small user group, evaluation plan and feedback process.']
    };
    planner.addEventListener('submit', e => {
      e.preventDefault(); if (!planner.reportValidity()) return;
      const values = Object.fromEntries(new FormData(planner));
      const answers = Object.values(values).map(Number);
      if (answers.length !== 5 || answers.some(v => ![0, 1, 2].includes(v))) return;
      const foundation = answers.includes(0), ready = answers.every(v => v === 2);
      const title = foundation ? 'Start with focused discovery.' : ready ? 'Plan a controlled delivery.' : 'Shape a small, useful pilot.';
      const description = foundation ? 'Some foundations still need attention. A focused discovery can clarify the use case, information, ownership and boundaries before you invest in a build.' : ready ? 'Your answers suggest that the main foundations have been considered. The next conversation should test assumptions, confirm feasibility and define a controlled pilot or delivery plan.' : 'You have a direction and some of the foundations. A bounded pilot can test value and feasibility while making the remaining decisions explicit.';
      document.querySelector('#ai-result-title').textContent = title;
      document.querySelector('#ai-result-description').textContent = description;
      const actions = document.querySelector('#ai-result-actions'); actions.replaceChildren();
      const tasks = Object.entries(values).filter(([,value]) => value !== '2').map(([key,value]) => recommendations[key][Number(value)]);
      if (ready) tasks.push('Validate the use case and data assumptions with a technical discovery.', 'Define a pilot with representative users, evaluation criteria and review gates.', 'Agree operating ownership, monitoring and a handover plan.');
      tasks.forEach(task => { const li = document.createElement('li'); li.textContent = task; actions.append(li); });
      planner.hidden = true; if (side) side.hidden = true; result.hidden = false; result.focus({preventScroll:true}); result.scrollIntoView?.({behavior:'smooth', block:'start'});
    });
    document.querySelector('#ai-reset').addEventListener('click', () => { result.hidden = true; planner.hidden = false; if (side) side.hidden = false; planner.querySelector('select').focus(); });
  }
  document.querySelectorAll('[data-print]').forEach(button => button.addEventListener('click', e => { e.preventDefault(); window.print(); }));
})();
