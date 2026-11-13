/* Page-only disclosure and section tracking. No changes to testimonial content. */
(() => {
  const root = document.querySelector('[data-year-nav]');
  if (!root) return;
  const toggle = root.querySelector('button');
  const panel = root.querySelector('nav');
  const current = root.querySelector('[data-year-current]');
  const links = Array.from(panel.querySelectorAll('a'));
  const sections = links.map(link => document.querySelector(link.hash));
  const header = document.querySelector('[data-site-header]');
  if (sections.some(section => !section)) return;
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
  const headerHeight = () => header ? header.getBoundingClientRect().height : 0;
  const close = (restoreFocus = false) => {
    toggle.setAttribute('aria-expanded', 'false');
    panel.hidden = true;
    if (restoreFocus) toggle.focus({ preventScroll: true });
  };
  const activate = (index) => {
    current.textContent = sections[index].dataset.section;
    links.forEach((link, i) => {
      if (i === index) link.setAttribute('aria-current', 'location');
      else link.removeAttribute('aria-current');
    });
  };
  toggle.addEventListener('click', () => {
    const opening = toggle.getAttribute('aria-expanded') !== 'true';
    toggle.setAttribute('aria-expanded', String(opening));
    panel.hidden = !opening;
  });
  document.addEventListener('pointerdown', event => {
    if (!root.contains(event.target)) close();
  });
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && !panel.hidden) { event.preventDefault(); event.stopImmediatePropagation(); close(true); }
  }, true);
  root.addEventListener('focusout', event => {
    if (event.relatedTarget && !root.contains(event.relatedTarget)) close();
  });
  // After choosing a year the menu keeps that year while the page scrolls there (and while lazy images above
  // nudge the section by a few pixels); it hands control back to scroll-tracking once the reader scrolls on.
  let lock = null;
  let minDistance = Infinity;
  let settleTimer;
  const targetTop = (i) => sections[i].getBoundingClientRect().top + window.scrollY - headerHeight() - 12;
  const settle = () => {
    if (lock === null) return;
    const drift = sections[lock].getBoundingClientRect().top - (headerHeight() + 12);
    if (Math.abs(drift) > 6) window.scrollTo({ top: targetTop(lock), behavior: 'instant' });
  };
  links.forEach((link, index) => link.addEventListener('click', event => {
    event.preventDefault();
    lock = index;
    minDistance = Infinity;
    window.scrollTo({ top: targetTop(index), behavior: reduced.matches ? 'instant' : 'smooth' });
    activate(index);
    window.history.replaceState(null, '', link.hash);
    close(true);
    window.clearTimeout(settleTimer);
    settleTimer = window.setTimeout(settle, 400);
  }));
  const sync = () => {
    if (lock !== null) return;
    // The current year is the last section whose top has passed a line a little below the sticky header.
    const boundary = headerHeight() + Math.min(160, window.innerHeight * 0.25);
    let index = 0;
    sections.forEach((section, i) => {
      if (section.getBoundingClientRect().top <= boundary) index = i;
    });
    activate(index);
  };
  // Any reader input (wheel, touch, keys, scrollbar) hands control back to scroll-tracking straight away.
  ['wheel', 'touchmove', 'keydown', 'pointerdown'].forEach(type => window.addEventListener(type, event => {
    if (lock === null || (type === 'pointerdown' && event.target.closest && event.target.closest('[data-year-nav]'))) return;
    if (type === 'keydown' && !['ArrowUp', 'ArrowDown', 'PageUp', 'PageDown', 'Home', 'End', ' '].includes(event.key)) return;
    lock = null;
    window.requestAnimationFrame(sync);
  }, { passive: true }));
  let ticking = false;
  window.addEventListener('scroll', () => {
    if (lock !== null) {
      window.clearTimeout(settleTimer);
      const distance = Math.abs(window.scrollY - targetTop(lock));
      if (distance < minDistance) minDistance = distance;
      // Moving away from the chosen year by more than a screen-edge's worth means the reader has scrolled on.
      if (distance - minDistance > 120) lock = null;
      else settleTimer = window.setTimeout(settle, 140);
    }
    if (ticking) return;
    ticking = true;
    window.requestAnimationFrame(() => { ticking = false; sync(); });
  }, { passive: true });
  let observer;
  const observe = () => {
    if (observer) observer.disconnect();
    if ('IntersectionObserver' in window) {
      // A narrow band below the sticky header fires only as cohort boundaries pass.
      observer = new IntersectionObserver(sync, {
        rootMargin: `-${headerHeight() + 12}px 0px -${Math.max(0, window.innerHeight - headerHeight() - 28)}px 0px`,
        threshold: 0,
      });
      sections.forEach(section => observer.observe(section));
    }
    sync();
  };
  let resizeTimer;
  window.addEventListener('resize', () => {
    window.clearTimeout(resizeTimer);
    resizeTimer = window.setTimeout(observe, 120);
  });
  window.addEventListener('hashchange', () => { lock = null; sync(); });
  root.hidden = false;
  observe();
})();
