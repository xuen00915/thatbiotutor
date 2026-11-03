'use strict';

const path = require('path');
const themeScript = process.argv[2];
if (!themeScript) throw new Error('Theme script path is required');

const api = require(path.resolve(themeScript));
const required = ['openMenu', 'closeMenu', 'toggleSubmenu', 'handleMenuKeydown', 'carouselIndex', 'swipeDirection', 'toggleFaq', 'prefersReducedMotion'];
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

process.stdout.write('PASS: interaction runtime\n');

