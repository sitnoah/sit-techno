const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { JSDOM } = require(process.env.SIT_TEST_MODULES ? path.join(process.env.SIT_TEST_MODULES, 'jsdom') : 'jsdom');
const root = path.join(__dirname, '..');
const dom = new JSDOM(fs.readFileSync(path.join(root, 'dist/index.html'), 'utf8'), {
  url: 'https://sit.example/', runScripts: 'outside-only'
});
const w = dom.window, d = w.document;
let resize;
w.matchMedia = () => ({ addEventListener: (name, callback) => { resize = callback; } });
w.HTMLElement.prototype.scrollIntoView = function () {};
for (const file of ['site.js', 'discovery.js']) w.eval(fs.readFileSync(path.join(root, 'wordpress/themes/sit-technology/assets', file), 'utf8'));
const toggle = d.querySelector('.menu-toggle'), nav = d.querySelector('#primary-nav');
const main = d.querySelector('#main'), footer = d.querySelector('.site-footer');
const key = (key, shiftKey = false) => {
  const event = new w.KeyboardEvent('keydown', { key, shiftKey, bubbles: true, cancelable: true });
  d.activeElement.dispatchEvent(event);
  return event;
};
// Opening isolates the page; closing preserves an inert state owned by another feature.
footer.setAttribute('inert', '');
toggle.focus(); toggle.click();
assert.equal(toggle.getAttribute('aria-expanded'), 'true');
assert.match(toggle.textContent, /Close/);
assert.ok(main.hasAttribute('inert'));
assert.ok(d.body.classList.contains('sit-menu-open'));
const lastLink = nav.querySelector('.nav-cta');
assert.equal(key('Tab', true).defaultPrevented, true);
assert.equal(d.activeElement, lastLink);
assert.equal(key('Tab').defaultPrevented, true);
assert.equal(d.activeElement, toggle);
d.querySelector('.expertise-toggle').click();
assert.equal(d.querySelector('#expertise-menu').hidden, false);
key('Escape');
assert.equal(toggle.getAttribute('aria-expanded'), 'false');
assert.match(toggle.textContent, /Menu/);
assert.equal(d.activeElement, toggle);
assert.equal(d.querySelector('#expertise-menu').hidden, true);
assert.equal(main.hasAttribute('inert'), false);
assert.equal(footer.hasAttribute('inert'), true);
assert.equal(d.body.classList.contains('sit-menu-open'), false);
footer.removeAttribute('inert');
// Search takes over from the mobile menu and returns focus to the visible menu button.
toggle.click();
d.querySelector('.search-open').click();
assert.equal(nav.classList.contains('is-open'), false);
assert.equal(main.hasAttribute('inert'), false);
assert.equal(d.querySelector('#site-search').hasAttribute('open'), true);
assert.equal(d.activeElement, d.querySelector('#site-search-input'));
d.querySelector('.search-close').click();
assert.equal(d.activeElement, toggle);
// Following a link or moving to desktop releases the lock and restores the page.
toggle.click();
const samePageLink = nav.querySelector('a.nav-link');
samePageLink.addEventListener('click', event => event.preventDefault());
samePageLink.click();
assert.equal(main.hasAttribute('inert'), false);
assert.equal(nav.classList.contains('is-open'), false);
toggle.click();
resize({ matches: true });
assert.equal(toggle.getAttribute('aria-expanded'), 'false');
assert.equal(d.body.classList.contains('sit-menu-open'), false);
assert.equal(d.querySelectorAll('[inert]').length, 0);
dom.window.close();
console.log('PASS: mobile menu isolation, focus loop, nested Escape, prior inert state, search handoff, link navigation and desktop resize.');
