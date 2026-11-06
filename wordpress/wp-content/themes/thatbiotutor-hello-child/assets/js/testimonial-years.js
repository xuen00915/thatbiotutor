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
  links.forEach((link, index) => link.addEventListener('click', event => {
    event.preventDefault();
    const top = sections[index].getBoundingClientRect().top + window.scrollY - headerHeight() - 12;
    window.scrollTo({ top, behavior: reduced.matches ? 'instant' : 'smooth' });
    activate(index);
    window.history.replaceState(null, '', link.hash);
    close(true);
  }));
  const sync = () => {
    const boundary = headerHeight() + 16;
    let index = 0;
    sections.forEach((section, i) => {
      if (section.getBoundingClientRect().top <= boundary) index = i;
    });
    activate(index);
  };
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
  window.addEventListener('hashchange', sync);
  root.hidden = false;
  observe();
})();
