/* Page-only disclosure and section tracking. No changes to testimonial content. */
(() => {
  const root = document.querySelector('[data-year-nav]');
  if (!root) return;
  const toggle = root.querySelector('button');
  const panel = root.querySelector('nav');
  const current = root.querySelector('[data-year-current]');
  const links = Array.from(panel.querySelectorAll('a.tbt-year-nav__year'));
  const sections = links.map(link => document.querySelector(link.hash));
  const header = document.querySelector('[data-site-header]');
  if (sections.some(section => !section)) return;
  // The Google reviews heading is rendered by the shared testimonial list: give each year's first one an id for the menu.
  sections.forEach(section => {
    const heading = section.querySelector('.tbt-google-review-heading');
    if (heading && !heading.id) heading.id = `${section.id}-google-reviews`;
  });
  const partLinks = Array.from(panel.querySelectorAll('a.tbt-year-nav__section'));
  partLinks.forEach(link => { if (!document.getElementById(link.hash.slice(1))) link.closest('li').hidden = true; });
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
  let lockEl = null;
  let minDistance = Infinity;
  let settleTimer;
  const targetTop = (el) => el.getBoundingClientRect().top + window.scrollY - headerHeight() - 12;
  const settle = () => {
    if (lock === null) return;
    const drift = lockEl.getBoundingClientRect().top - (headerHeight() + 12);
    if (Math.abs(drift) > 6) window.scrollTo({ top: targetTop(lockEl), behavior: 'instant' });
  };
  const jump = (link, index, element) => {
    lock = index;
    lockEl = element;
    minDistance = Infinity;
    window.scrollTo({ top: targetTop(element), behavior: reduced.matches ? 'instant' : 'smooth' });
    activate(index);
    window.history.replaceState(null, '', link.hash);
    close(true);
    window.clearTimeout(settleTimer);
    settleTimer = window.setTimeout(settle, 400);
  };
  links.forEach((link, index) => link.addEventListener('click', event => {
    event.preventDefault();
    jump(link, index, sections[index]);
  }));
  partLinks.forEach(link => link.addEventListener('click', event => {
    event.preventDefault();
    const element = document.getElementById(link.hash.slice(1));
    const index = links.findIndex(year => year.closest('.tbt-year-nav__group').contains(link));
    if (!element || index < 0) return;
    // Reviews live in a scrolling list: scroll that list to the part first, then bring the list under the header.
    const scroller = element.closest('[data-testimonial-list]');
    if (scroller && scroller.scrollHeight > scroller.clientHeight) {
      scroller.scrollTop += element.getBoundingClientRect().top - scroller.getBoundingClientRect().top - 8;
      jump(link, index, scroller);
    } else {
      jump(link, index, element);
    }
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
      const distance = Math.abs(window.scrollY - targetTop(lockEl));
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
