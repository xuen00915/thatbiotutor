'use strict';

const path = require('path');
const themeScript = process.argv[2];
if (!themeScript) throw new Error('Theme script path is required');

const api = require(path.resolve(themeScript));
const required = ['openMenu', 'closeMenu', 'toggleSubmenu', 'handleMenuKeydown', 'carouselIndex', 'swipeDirection', 'toggleFaq', 'prefersReducedMotion', 'formatCountUpValue'];
for (const name of required) {
  if (typeof api[name] !== 'function') throw new Error(`${name} must be exported`);
}

class MockElement {
  constructor() { this.hidden = true; this.attributes = new Map(); this.focused = false; }
  setAttribute(name, value) { this.attributes.set(name, String(value)); }
  getAttribute(name) { return this.attributes.get(name) ?? null; }
  focus() { this.focused = true; }
}

const menu = new MockElement();
const toggle = new MockElement();
toggle.setAttribute('aria-expanded', 'false');
api.openMenu(menu, toggle);
if (menu.hidden || toggle.getAttribute('aria-expanded') !== 'true') throw new Error('openMenu state failed');
api.closeMenu(menu, toggle, true);
if (!menu.hidden || toggle.getAttribute('aria-expanded') !== 'false' || !toggle.focused) throw new Error('closeMenu/focus return failed');

menu.hidden = false;
toggle.focused = false;
let prevented = false;
api.handleMenuKeydown({ key: 'Escape', preventDefault() { prevented = true; } }, menu, toggle);
if (!menu.hidden || !toggle.focused || !prevented) throw new Error('Escape path failed');

const submenu = new MockElement();
const submenuToggle = new MockElement();
submenuToggle.setAttribute('aria-expanded', 'false');
api.toggleSubmenu(submenu, submenuToggle);
if (submenu.hidden || submenuToggle.getAttribute('aria-expanded') !== 'true') throw new Error('submenu touch/keyboard state failed');

if (api.carouselIndex(0, -1, 4) !== 3 || api.carouselIndex(3, 1, 4) !== 0) throw new Error('carousel previous/next wrapping failed');
if (api.swipeDirection(200, 100, 40) !== 1 || api.swipeDirection(100, 200, 40) !== -1 || api.swipeDirection(100, 120, 40) !== 0) throw new Error('carousel swipe path failed');

const faqButton = new MockElement();
const faqPanel = new MockElement();
faqButton.setAttribute('aria-expanded', 'false');
api.toggleFaq(faqButton, faqPanel);
if (faqPanel.hidden || faqButton.getAttribute('aria-expanded') !== 'true') throw new Error('FAQ ARIA state failed');

if (!api.prefersReducedMotion(() => ({ matches: true }))) throw new Error('Reduced-motion detection failed');
if (api.prefersReducedMotion(() => ({ matches: false }))) throw new Error('Normal-motion detection failed');

if (api.formatCountUpValue(912, '912+') !== '912+') throw new Error('Integer count-up formatting failed');
if (api.formatCountUpValue(2.3, '2.3') !== '2.3') throw new Error('Decimal count-up formatting failed');
if (api.formatCountUpValue(1, '1 in 2') !== '1 in 2') throw new Error('Ratio count-up formatting failed');

if (typeof api.syncNavigationViewport !== 'function') throw new Error('syncNavigationViewport must be exported');
const resizeMenu = new MockElement();
const resizeToggle = new MockElement();
const resizeSubmenu = new MockElement();
const resizeSubmenuToggle = new MockElement();
resizeMenu.hidden = false;
resizeToggle.setAttribute('aria-expanded', 'true');
resizeSubmenu.hidden = false;
resizeSubmenuToggle.setAttribute('aria-expanded', 'true');
api.syncNavigationViewport(resizeMenu, resizeToggle, [{ menu: resizeSubmenu, toggle: resizeSubmenuToggle }], true);
if (!resizeMenu.hidden || resizeToggle.getAttribute('aria-expanded') !== 'false') throw new Error('mobile resize must reset the main menu');
if (!resizeSubmenu.hidden || resizeSubmenuToggle.getAttribute('aria-expanded') !== 'false') throw new Error('mobile resize must reset open submenus');
api.syncNavigationViewport(resizeMenu, resizeToggle, [{ menu: resizeSubmenu, toggle: resizeSubmenuToggle }], false);
if (resizeMenu.hidden || resizeToggle.getAttribute('aria-expanded') !== 'false') throw new Error('desktop resize must show the main menu without reporting it expanded');

process.stdout.write('PASS: interaction runtime\n');

