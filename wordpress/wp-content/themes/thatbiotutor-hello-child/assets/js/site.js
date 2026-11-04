(function (root, factory) {
  'use strict';
  const api = factory();
  if (typeof module === 'object' && module.exports) module.exports = api;
  if (root) root.TBTInteractions = api;
  if (root && root.document) {
    const start = () => api.init(root.document, root);
    if (root.document.readyState === 'loading') root.document.addEventListener('DOMContentLoaded', start, { once: true });
    else start();
  }
})(typeof window !== 'undefined' ? window : globalThis, function () {
  'use strict';

  function openMenu(menu, toggle) {
    menu.hidden = false;
    toggle.setAttribute('aria-expanded', 'true');
  }

  function closeMenu(menu, toggle, returnFocus) {
    menu.hidden = true;
    toggle.setAttribute('aria-expanded', 'false');
    if (returnFocus && typeof toggle.focus === 'function') toggle.focus();
  }

  function toggleSubmenu(menu, toggle) {
    const opening = toggle.getAttribute('aria-expanded') !== 'true';
    menu.hidden = !opening;
    toggle.setAttribute('aria-expanded', opening ? 'true' : 'false');
    return opening;
  }

  function handleMenuKeydown(event, menu, toggle) {
    if (event.key !== 'Escape') return false;
    event.preventDefault();
    closeMenu(menu, toggle, true);
    return true;
  }

  function carouselIndex(current, delta, total) {
    if (total <= 0) return 0;
    return (current + delta + total) % total;
  }

  function swipeDirection(startX, endX, threshold) {
    const distance = startX - endX;
    if (Math.abs(distance) < threshold) return 0;
    return distance > 0 ? 1 : -1;
  }

  function toggleFaq(button, panel) {
    const opening = button.getAttribute('aria-expanded') !== 'true';
    button.setAttribute('aria-expanded', opening ? 'true' : 'false');
    panel.hidden = !opening;
    return opening;
  }

  function prefersReducedMotion(matchMedia) {
    if (typeof matchMedia !== 'function') return false;
    return Boolean(matchMedia('(prefers-reduced-motion: reduce)').matches);
  }

  function syncNavigationViewport(menu, toggle, submenuPairs, mobile) {
    menu.hidden = mobile;
    toggle.setAttribute('aria-expanded', 'false');
    submenuPairs.forEach((pair) => {
      pair.menu.hidden = true;
      pair.toggle.setAttribute('aria-expanded', 'false');
    });
  }

  function initNavigation(document, windowObject) {
    const toggle = document.querySelector('[data-menu-toggle]');
    const menu = document.querySelector('[data-menu]');
    if (!toggle || !menu) return;
    const submenuPairs = Array.from(document.querySelectorAll('[data-submenu-toggle]')).map((button) => ({
      toggle: button,
      menu: document.getElementById(button.getAttribute('aria-controls')),
    })).filter((pair) => pair.menu);
    const media = windowObject.matchMedia && windowObject.matchMedia('(max-width: 980px)');
    const syncViewport = (event) => {
      syncNavigationViewport(menu, toggle, submenuPairs, Boolean(event.matches));
    };
    syncViewport(media || { matches: false });
    if (media && typeof media.addEventListener === 'function') media.addEventListener('change', syncViewport);
    else if (media && typeof media.addListener === 'function') media.addListener(syncViewport);
    toggle.addEventListener('click', () => {
      if (toggle.getAttribute('aria-expanded') === 'true') closeMenu(menu, toggle, true);
      else openMenu(menu, toggle);
    });
    document.addEventListener('keydown', (event) => handleMenuKeydown(event, menu, toggle));
    document.addEventListener('click', (event) => {
      if (toggle.getAttribute('aria-expanded') === 'true' && !menu.contains(event.target) && !toggle.contains(event.target)) closeMenu(menu, toggle, false);
    });
    submenuPairs.forEach(({ toggle: button, menu: submenu }) => {
      submenu.hidden = true;
      button.addEventListener('click', () => toggleSubmenu(submenu, button));
      button.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          toggleSubmenu(submenu, button);
          const firstLink = submenu.querySelector('a');
          if (!submenu.hidden && firstLink) firstLink.focus();
        }
      });
    });
  }

  function initCarousels(document, windowObject) {
    document.querySelectorAll('[data-carousel]').forEach((carousel) => {
      const slides = Array.from(carousel.querySelectorAll('[data-carousel-slide]'));
      const dots = Array.from(carousel.querySelectorAll('[data-carousel-dot]'));
      const status = carousel.querySelector('[data-carousel-status]');
      let index = 0;
      let touchStart = 0;
      let paused = false;
      const show = (next) => {
        index = carouselIndex(next, 0, slides.length);
        slides.forEach((slide, slideIndex) => { slide.hidden = slideIndex !== index; });
        dots.forEach((dot, dotIndex) => dot.setAttribute('aria-current', dotIndex === index ? 'true' : 'false'));
        if (status) status.textContent = `${index + 1} of ${slides.length}`;
      };
      const move = (delta) => show(carouselIndex(index, delta, slides.length));
      const previous = carousel.querySelector('[data-carousel-prev]');
      const next = carousel.querySelector('[data-carousel-next]');
      if (previous) previous.addEventListener('click', () => move(-1));
      if (next) next.addEventListener('click', () => move(1));
      dots.forEach((dot, dotIndex) => dot.addEventListener('click', () => show(dotIndex)));
      carousel.addEventListener('touchstart', (event) => { touchStart = event.changedTouches[0].clientX; }, { passive: true });
      carousel.addEventListener('touchend', (event) => {
        const direction = swipeDirection(touchStart, event.changedTouches[0].clientX, 40);
        if (direction) move(direction);
      }, { passive: true });
      show(0);
      const autoplay = Number(carousel.getAttribute('data-carousel-autoplay') || 0);
      if (autoplay > 0 && !prefersReducedMotion(windowObject.matchMedia && windowObject.matchMedia.bind(windowObject))) {
        const pause = () => { paused = true; };
        const resume = (event) => {
          if (event && event.relatedTarget && carousel.contains(event.relatedTarget)) return;
          paused = false;
        };
        carousel.addEventListener('mouseenter', pause);
        carousel.addEventListener('mouseleave', resume);
        carousel.addEventListener('focusin', pause);
        carousel.addEventListener('focusout', resume);
        windowObject.setInterval(() => {
          if (!paused && !document.documentElement.classList.contains('tbt-lightbox-open')) move(1);
        }, autoplay);
      }
    });
  }

  function initCertificateLightbox(document) {
    const triggers = Array.from(document.querySelectorAll('[data-certificate-lightbox-trigger]'));
    if (!triggers.length || !document.body) return;
    const dialog = document.createElement('dialog');
    dialog.className = 'tbt-certificate-lightbox';
    dialog.setAttribute('aria-label', 'Enlarged certificate');
    dialog.innerHTML = '<button class="tbt-certificate-lightbox__close" type="button" data-certificate-lightbox-close aria-label="Close enlarged certificate">&times;</button><div class="tbt-certificate-lightbox__stage"><img alt=""></div>';
    document.body.appendChild(dialog);
    const image = dialog.querySelector('img');
    const closeButton = dialog.querySelector('[data-certificate-lightbox-close]');
    let activeTrigger = null;
    const close = () => {
      if (!dialog.open) return;
      if (typeof dialog.close === 'function') dialog.close();
      else dialog.removeAttribute('open');
    };
    triggers.forEach((trigger) => {
      trigger.addEventListener('click', () => {
        const source = trigger.querySelector('img');
        if (!source || !image) return;
        activeTrigger = trigger;
        image.src = source.currentSrc || source.src;
        image.alt = source.alt || 'Enlarged certificate';
        document.documentElement.classList.add('tbt-lightbox-open');
        if (typeof dialog.showModal === 'function') dialog.showModal();
        else dialog.setAttribute('open', '');
      });
    });
    if (closeButton) closeButton.addEventListener('click', close);
    dialog.addEventListener('click', (event) => { if (event.target === dialog) close(); });
    dialog.addEventListener('close', () => {
      document.documentElement.classList.remove('tbt-lightbox-open');
      if (activeTrigger && typeof activeTrigger.focus === 'function') activeTrigger.focus();
      activeTrigger = null;
    });
  }

  function initFaqs(document) {
    document.querySelectorAll('[data-faq-button]').forEach((button) => {
      const panel = document.getElementById(button.getAttribute('aria-controls'));
      if (!panel) return;
      panel.hidden = true;
      button.addEventListener('click', () => toggleFaq(button, panel));
    });
  }

  function init(document, windowObject) {
    document.documentElement.classList.remove('no-js');
    document.documentElement.classList.add('js');
    initNavigation(document, windowObject);
    initCarousels(document, windowObject);
    initCertificateLightbox(document);
    initFaqs(document);
  }

  return { openMenu, closeMenu, toggleSubmenu, handleMenuKeydown, carouselIndex, swipeDirection, toggleFaq, prefersReducedMotion, syncNavigationViewport, initCertificateLightbox, init };
});

