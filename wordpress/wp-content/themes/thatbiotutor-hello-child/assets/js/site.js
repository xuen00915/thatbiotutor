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

  function formatCountUpValue(value, display) {
    const finalDisplay = String(display || '');
    if (finalDisplay.includes(' in ')) return value >= 1 ? finalDisplay : '0';
    if (finalDisplay.includes('.')) {
      const decimals = (finalDisplay.split('.')[1] || '').replace(/\D/g, '').length;
      return Number(value).toFixed(decimals);
    }
    const suffix = finalDisplay.endsWith('%') ? '%' : (finalDisplay.endsWith('+') ? '+' : '');
    return `${Math.round(Number(value))}${suffix}`;
  }

  function initCountUps(document, windowObject) {
    const counters = Array.from(document.querySelectorAll('[data-count-up]'));
    if (!counters.length) return;
    const reveal = (counter, animate) => {
      if (counter.getAttribute('data-count-complete') === 'true') return;
      const target = Number(counter.getAttribute('data-count-target') || 0);
      const display = counter.getAttribute('data-count-display') || String(target);
      const duration = animate ? 1200 : 0;
      const start = windowObject.performance && typeof windowObject.performance.now === 'function' ? windowObject.performance.now() : 0;
      const step = (time) => {
        const progress = duration === 0 ? 1 : Math.min(1, (time - start) / duration);
        const eased = 1 - Math.pow(1 - progress, 3);
        counter.textContent = formatCountUpValue(target * eased, display);
        if (progress < 1 && typeof windowObject.requestAnimationFrame === 'function') windowObject.requestAnimationFrame(step);
        else {
          counter.textContent = display;
          counter.setAttribute('data-count-complete', 'true');
        }
      };
      if (duration > 0 && typeof windowObject.requestAnimationFrame === 'function') windowObject.requestAnimationFrame(step);
      else step(start);
    };
    const reduceMotion = prefersReducedMotion(windowObject.matchMedia && windowObject.matchMedia.bind(windowObject));
    if (reduceMotion || typeof windowObject.IntersectionObserver !== 'function') {
      counters.forEach((counter) => reveal(counter, false));
      return;
    }
    const observer = new windowObject.IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        reveal(entry.target, true);
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.4 });
    counters.forEach((counter) => {
      counter.textContent = formatCountUpValue(0, counter.getAttribute('data-count-display') || '0');
      observer.observe(counter);
    });
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
    let lastMobile = windowObject.innerWidth <= 980;
    windowObject.addEventListener('resize', () => {
      const mobile = windowObject.innerWidth <= 980;
      if (mobile === lastMobile) return;
      lastMobile = mobile;
      syncViewport({ matches: mobile });
    });
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
      const fade = carousel.hasAttribute('data-carousel-fade');
      if (fade) {
        // Crossfade: every slide stays rendered and preloaded, so switching never shows a blank frame.
        slides.forEach((slide) => {
          slide.hidden = false;
          slide.querySelectorAll('img').forEach((image) => { image.loading = 'eager'; });
        });
      }
      const show = (next) => {
        index = carouselIndex(next, 0, slides.length);
        slides.forEach((slide, slideIndex) => {
          const active = slideIndex === index;
          slide.classList.toggle('is-active', active);
          if (fade) slide.setAttribute('aria-hidden', active ? 'false' : 'true');
          else slide.hidden = !active;
        });
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
    dialog.innerHTML = '<button class="tbt-certificate-lightbox__close" type="button" data-certificate-lightbox-close aria-label="Close enlarged certificate">&times;</button><button class="tbt-certificate-lightbox__control tbt-certificate-lightbox__control--previous" type="button" data-certificate-lightbox-prev aria-label="Previous certificate"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg></button><div class="tbt-certificate-lightbox__stage"><img alt=""></div><button class="tbt-certificate-lightbox__control tbt-certificate-lightbox__control--next" type="button" data-certificate-lightbox-next aria-label="Next certificate"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg></button><p class="screen-reader-text" data-certificate-lightbox-status aria-live="polite"></p>';
    document.body.appendChild(dialog);
    const image = dialog.querySelector('img');
    const closeButton = dialog.querySelector('[data-certificate-lightbox-close]');
    const previousButton = dialog.querySelector('[data-certificate-lightbox-prev]');
    const nextButton = dialog.querySelector('[data-certificate-lightbox-next]');
    const status = dialog.querySelector('[data-certificate-lightbox-status]');
    let openingTrigger = null;
    let activeGroup = [];
    let activeIndex = 0;
    const show = (nextIndex) => {
      if (!activeGroup.length || !image) return;
      activeIndex = carouselIndex(nextIndex, 0, activeGroup.length);
      const source = activeGroup[activeIndex].querySelector('img');
      if (!source) return;
      image.src = source.currentSrc || source.src;
      image.alt = source.alt || 'Enlarged certificate';
      const hasMultiple = activeGroup.length > 1;
      if (previousButton) previousButton.hidden = !hasMultiple;
      if (nextButton) nextButton.hidden = !hasMultiple;
      if (status) status.textContent = `${activeIndex + 1} of ${activeGroup.length}`;
    };
    const move = (delta) => show(activeIndex + delta);
    const close = () => {
      if (!dialog.open) return;
      if (typeof dialog.close === 'function') dialog.close();
      else dialog.removeAttribute('open');
    };
    triggers.forEach((trigger) => {
      trigger.addEventListener('click', () => {
        const certificateViewer = trigger.closest('.tbt-tutor__documents');
        activeGroup = certificateViewer ? Array.from(certificateViewer.querySelectorAll('[data-certificate-lightbox-trigger]')) : [trigger];
        openingTrigger = trigger;
        show(Math.max(0, activeGroup.indexOf(trigger)));
        document.documentElement.classList.add('tbt-lightbox-open');
        if (typeof dialog.showModal === 'function') dialog.showModal();
        else dialog.setAttribute('open', '');
      });
    });
    if (closeButton) closeButton.addEventListener('click', close);
    if (previousButton) previousButton.addEventListener('click', () => move(-1));
    if (nextButton) nextButton.addEventListener('click', () => move(1));
    dialog.addEventListener('keydown', (event) => {
      if (activeGroup.length < 2) return;
      if (event.key === 'ArrowLeft') { event.preventDefault(); move(-1); }
      if (event.key === 'ArrowRight') { event.preventDefault(); move(1); }
    });
    dialog.addEventListener('click', (event) => { if (event.target === dialog) close(); });
    dialog.addEventListener('close', () => {
      document.documentElement.classList.remove('tbt-lightbox-open');
      if (openingTrigger && typeof openingTrigger.focus === 'function') openingTrigger.focus();
      openingTrigger = null;
      activeGroup = [];
      activeIndex = 0;
    });
  }

  function initFaqs(document, windowObject) {
    const reduce = prefersReducedMotion(windowObject && windowObject.matchMedia ? windowObject.matchMedia.bind(windowObject) : undefined);
    document.querySelectorAll('[data-faq-button]').forEach((button) => {
      const panel = document.getElementById(button.getAttribute('aria-controls'));
      if (!panel) return;
      panel.hidden = true;
      button.addEventListener('click', () => {
        if (reduce || typeof panel.animate !== 'function') {
          toggleFaq(button, panel);
          return;
        }
        const opening = button.getAttribute('aria-expanded') !== 'true';
        panel.getAnimations().forEach((animation) => animation.cancel());
        panel.style.overflow = 'hidden';
        if (opening) {
          toggleFaq(button, panel);
          const height = panel.scrollHeight;
          panel.animate([{ height: '0px', opacity: 0 }, { height: height + 'px', opacity: 1 }], { duration: 320, easing: 'ease' }).onfinish = () => { panel.style.overflow = ''; };
        } else {
          button.setAttribute('aria-expanded', 'false');
          const height = panel.getBoundingClientRect().height;
          panel.animate([{ height: height + 'px', opacity: 1 }, { height: '0px', opacity: 0 }], { duration: 260, easing: 'ease' }).onfinish = () => {
            panel.hidden = true;
            panel.style.overflow = '';
          };
        }
      });
    });
  }

  function initReviewDisclosure(document) {
    // Long review lists open with the first few; every review stays in the page and can be revealed.
    const initial = 4;
    const batch = 12;
    document.querySelectorAll('[data-testimonial-list]').forEach((list, listIndex) => {
      const items = Array.from(list.querySelectorAll(':scope > .tbt-testimonial'));
      if (items.length <= initial + 2) return;
      if (!list.id) list.id = 'tbt-review-list-' + (listIndex + 1);
      items.slice(initial).forEach((item) => { item.hidden = true; });
      const controls = document.createElement('div');
      controls.className = 'tbt-reviews-controls';
      const more = document.createElement('button');
      more.type = 'button';
      more.className = 'tbt-reviews-controls__more';
      more.setAttribute('data-reviews-more', '');
      more.setAttribute('aria-controls', list.id);
      const all = document.createElement('button');
      all.type = 'button';
      all.className = 'tbt-reviews-controls__all';
      all.setAttribute('data-reviews-all', '');
      all.setAttribute('aria-controls', list.id);
      controls.append(more, all);
      list.after(controls);
      const refresh = () => {
        const hiddenItems = items.filter((item) => item.hidden);
        if (!hiddenItems.length) { controls.hidden = true; all.hidden = true; more.hidden = true; return; }
        more.textContent = 'Show ' + Math.min(batch, hiddenItems.length) + ' more reviews';
        all.textContent = 'Show all ' + items.length + ' reviews';
        all.hidden = hiddenItems.length <= batch;
      };
      const reveal = (count) => {
        items.filter((item) => item.hidden).slice(0, count).forEach((item) => { item.hidden = false; item.classList.add('is-revealed'); });
        refresh();
      };
      more.addEventListener('click', () => reveal(batch));
      all.addEventListener('click', () => reveal(items.length));
      refresh();
    });
  }

  function initHeaderScroll(document, windowObject) {
    const header = document.querySelector('.tbt-landing-header');
    if (!header) return;
    const update = () => header.classList.toggle('is-scrolled', (windowObject.scrollY || 0) > 8);
    windowObject.addEventListener('scroll', update, { passive: true });
    update();
  }

  function initReveal(document, windowObject) {
    const targets = Array.from(document.querySelectorAll('#main-content > section, #main-content > div > section, .site-footer'));
    const reduce = prefersReducedMotion(windowObject.matchMedia ? windowObject.matchMedia.bind(windowObject) : undefined);
    if (!targets.length) return;
    if (reduce || typeof windowObject.IntersectionObserver !== 'function') return;
    const observer = new windowObject.IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      });
    }, { rootMargin: '0px 0px -6% 0px', threshold: 0 });
    targets.forEach((target) => {
      target.classList.add('tbt-reveal');
      observer.observe(target);
    });
  }

  function initHomeProof(document, windowObject) {
    const crests = document.querySelector('.tbt-home-proof__crests');
    if (crests) {
      const motion = windowObject.matchMedia('(prefers-reduced-motion: reduce)');
      let paused = motion.matches;
      let hovering = false;
      let previousTime = 0;
      let offset = 0;
      const originals = Array.from(crests.children);
      originals.forEach((image) => {
        const clone = image.cloneNode(true);
        clone.setAttribute('aria-hidden', 'true');
        clone.removeAttribute('data-media-key');
        clone.alt = '';
        crests.appendChild(clone);
      });
      crests.addEventListener('mouseenter', () => { hovering = true; });
      crests.addEventListener('mouseleave', () => { hovering = false; });
      motion.addEventListener('change', () => { paused = motion.matches; });
      const tick = (time) => {
        const delta = previousTime ? Math.min(time - previousTime, 50) : 0;
        previousTime = time;
        if (!paused && !hovering && !document.hidden && originals.length) {
          const cycle = crests.children[originals.length].offsetLeft - originals[0].offsetLeft;
          if (cycle > 0) {
            offset = (offset + delta * 0.035) % cycle;
            crests.scrollLeft = offset;
          }
        }
        windowObject.requestAnimationFrame(tick);
      };
      windowObject.requestAnimationFrame(tick);
    }
    document.querySelectorAll('[data-google-reviews]').forEach((widget) => {
      const track = widget.querySelector('[data-review-track]');
      const move = (direction) => {
        const reduced = prefersReducedMotion(windowObject.matchMedia.bind(windowObject));
        const distance = track.firstElementChild.getBoundingClientRect().width + 16;
        track.scrollBy({ left: direction * distance, behavior: reduced ? 'instant' : 'smooth' });
      };
      widget.querySelector('[data-review-prev]').addEventListener('click', () => move(-1));
      widget.querySelector('[data-review-next]').addEventListener('click', () => move(1));
      widget.querySelectorAll('[data-review-expand]').forEach((button) => {
        button.addEventListener('click', () => {
          const expanded = button.getAttribute('aria-expanded') !== 'true';
          button.setAttribute('aria-expanded', String(expanded));
          button.textContent = expanded ? 'Read less' : 'Read more';
          document.getElementById(button.getAttribute('aria-controls')).classList.toggle('is-expanded', expanded);
        });
      });
    });
  }

  function init(document, windowObject) {
    document.documentElement.classList.remove('no-js');
    document.documentElement.classList.add('js');
    initNavigation(document, windowObject);
    initCarousels(document, windowObject);
    initCountUps(document, windowObject);
    initCertificateLightbox(document);
    initFaqs(document, windowObject);
    initHeaderScroll(document, windowObject);
    initReviewDisclosure(document);
    initReveal(document, windowObject);
    initHomeProof(document, windowObject);
  }

  return { openMenu, closeMenu, toggleSubmenu, handleMenuKeydown, carouselIndex, swipeDirection, toggleFaq, prefersReducedMotion, formatCountUpValue, syncNavigationViewport, initCertificateLightbox, init };
});

