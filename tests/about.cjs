const assert = require('node:assert/strict'), fs = require('node:fs'), path = require('node:path');
const modules = process.env.SIT_TEST_MODULES;
const { JSDOM } = require(modules ? path.join(modules, 'jsdom') : 'jsdom');
const root = path.join(__dirname, '..');
const dom = new JSDOM(fs.readFileSync(path.join(root, 'dist/about/index.html'), 'utf8'), {url:'https://sit.example/about/', runScripts:'outside-only'});
const w = dom.window, d = w.document;
w.matchMedia = () => ({addEventListener(){}});
for (const name of ['site','discovery']) w.eval(fs.readFileSync(path.join(root, 'wordpress/themes/sit-technology/assets/'+name+'.js'), 'utf8'));
const tabs = [...d.querySelectorAll('.about-role-tabs [role=tab]')];
const panels = [...d.querySelectorAll('.about-role-panels [role=tabpanel]')];
assert.equal(tabs.length, 3);
for (const [index, tab] of tabs.entries()) {
  tab.click();
  assert.equal(panels.filter(panel => !panel.hidden).length, 1);
  assert.equal(panels[index].hidden, false);
  assert.equal(tab.getAttribute('aria-selected'), 'true');
}
tabs[2].dispatchEvent(new w.KeyboardEvent('keydown', {key:'Home', bubbles:true, cancelable:true}));
assert.equal(d.activeElement, tabs[0]);
tabs[0].dispatchEvent(new w.KeyboardEvent('keydown', {key:'ArrowDown', bubbles:true, cancelable:true}));
assert.equal(d.activeElement, tabs[1]);
assert.equal(panels[1].hidden, false);
tabs[1].dispatchEvent(new w.KeyboardEvent('keydown', {key:'End', bubbles:true, cancelable:true}));
assert.equal(d.activeElement, tabs[2]);
const questions = [...d.querySelectorAll('.about-faq details')];
assert.equal(questions.length, 6);
for (const question of questions) {
  question.querySelector('summary').click(); assert.equal(question.open, true);
  question.querySelector('summary').click(); assert.equal(question.open, false);
}
assert.equal(d.querySelectorAll('.about-resource-list > a').length, 3);
for (const link of d.querySelectorAll('.about-jump a')) assert.ok(d.querySelector(new URL(link.href).hash));
dom.window.close();
console.log('PASS: three delivery views, keyboard selection, six FAQ disclosures, resource links and in-page navigation.');
