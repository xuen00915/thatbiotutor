(function () {
  "use strict";

  const reducedMotionQuery = window.matchMedia(
    "(prefers-reduced-motion: reduce)"
  );

  function buildWhatsAppUrl(message) {
    const config = window.TBT_CONTENT.whatsapp;
    const params = new URLSearchParams({ text: message });
    return `https://wa.me/${config.number}?${params.toString()}`;
  }

  function initWhatsAppLinks(root = document) {
    root.querySelectorAll("[data-whatsapp-message]").forEach((link) => {
      const messageKey = link.dataset.whatsappMessage;
      const message = window.TBT_CONTENT.whatsapp[messageKey];
      if (message) {
        link.href = buildWhatsAppUrl(message);
      }
    });
  }

  function initCarousels(root = document) {
    root.querySelectorAll(".media-carousel").forEach((carousel) => {
      if (carousel.dataset.carouselReady === "true") return;

      const slides = Array.from(
        carousel.querySelectorAll("[data-carousel-slide]")
      );
      if (!slides.length) return;

      const previous = carousel.querySelector("[data-carousel-prev]");
      const next = carousel.querySelector("[data-carousel-next]");
      const dots = Array.from(
        carousel.querySelectorAll("[data-carousel-dot]")
      );
      const status = carousel.querySelector("[data-carousel-status]");
      let activeIndex = Math.max(
        0,
        slides.findIndex((slide) => slide.classList.contains("is-active"))
      );
      let pointerStartX = null;
      let pointerStartY = null;

      carousel.tabIndex = 0;
      carousel.setAttribute("role", "region");
      carousel.setAttribute("aria-roledescription", "carousel");

      function showSlide(nextIndex, announce = true) {
        activeIndex = (nextIndex + slides.length) % slides.length;
        slides.forEach((slide, index) => {
          const active = index === activeIndex;
          slide.classList.toggle("is-active", active);
          slide.setAttribute("aria-hidden", String(!active));
          slide.tabIndex = -1;
        });
        dots.forEach((dot, index) => {
          const active = index === activeIndex;
          dot.classList.toggle("is-active", active);
          if (active) {
            dot.setAttribute("aria-current", "true");
          } else {
            dot.removeAttribute("aria-current");
          }
        });
        if (status && announce) {
          status.textContent = `Image ${activeIndex + 1} of ${slides.length}`;
        }
      }

      previous?.addEventListener("click", () => showSlide(activeIndex - 1));
      next?.addEventListener("click", () => showSlide(activeIndex + 1));
      dots.forEach((dot, index) => {
        dot.addEventListener("click", () => showSlide(index));
      });

      carousel.addEventListener("keydown", (event) => {
        if (event.key === "ArrowLeft") {
          event.preventDefault();
          showSlide(activeIndex - 1);
        }
        if (event.key === "ArrowRight") {
          event.preventDefault();
          showSlide(activeIndex + 1);
        }
        if (event.key === "Home") {
          event.preventDefault();
          showSlide(0);
        }
        if (event.key === "End") {
          event.preventDefault();
          showSlide(slides.length - 1);
        }
      });

      carousel.addEventListener("pointerdown", (event) => {
        if (!event.isPrimary) return;
        pointerStartX = event.clientX;
        pointerStartY = event.clientY;
      });
      carousel.addEventListener("pointerup", (event) => {
        if (
          pointerStartX === null ||
          pointerStartY === null ||
          !event.isPrimary
        ) {
          return;
        }
        const distance = event.clientX - pointerStartX;
        const distanceY = event.clientY - pointerStartY;
        pointerStartX = null;
        pointerStartY = null;
        if (
          Math.abs(distance) < 48 ||
          Math.abs(distance) <= Math.abs(distanceY) * 1.15
        ) {
          return;
        }
        showSlide(activeIndex + (distance < 0 ? 1 : -1));
      });
      carousel.addEventListener("pointercancel", () => {
        pointerStartX = null;
        pointerStartY = null;
      });

      carousel.classList.add("is-enhanced");
      carousel.dataset.carouselReady = "true";
      showSlide(activeIndex, false);
    });
  }

  function formatCounter(counter, value) {
    const decimals = Number.isInteger(counter.value) ? 0 : 1;
    const formatted = Number(value).toFixed(decimals);
    return `${counter.prefix || ""}${formatted}${counter.suffix || ""}`;
  }

  function initCounters(root = document) {
    const configuredCounters = new Map(
      window.TBT_CONTENT.counters.map((counter) => [counter.key, counter])
    );
    const elements = Array.from(root.querySelectorAll("[data-counter-key]"));
    const showFinal = (element) => {
      const counter = configuredCounters.get(element.dataset.counterKey);
      if (counter) element.textContent = formatCounter(counter, counter.value);
    };

    if (
      reducedMotionQuery.matches ||
      !("IntersectionObserver" in window) ||
      !("requestAnimationFrame" in window)
    ) {
      elements.forEach(showFinal);
      return;
    }

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          const element = entry.target;
          const counter = configuredCounters.get(element.dataset.counterKey);
          observer.unobserve(element);
          if (!counter || element.dataset.counterAnimated === "true") {
            showFinal(element);
            return;
          }

          element.dataset.counterAnimated = "true";
          const duration = 900;
          const started = performance.now();
          function tick(now) {
            const progress = Math.min(1, (now - started) / duration);
            const eased = 1 - Math.pow(1 - progress, 3);
            element.textContent = formatCounter(
              counter,
              counter.value * eased
            );
            if (progress < 1) {
              requestAnimationFrame(tick);
            } else {
              showFinal(element);
            }
          }
          requestAnimationFrame(tick);
        });
      },
      { threshold: 0.35 }
    );

    elements.forEach((element) => observer.observe(element));
  }

  function initFaqs(root = document) {
    root.querySelectorAll(".faq").forEach((faq) => {
      if (faq.dataset.faqReady === "true") return;
      const button = faq.querySelector("[data-faq-button]");
      const panel = faq.querySelector("[data-faq-panel]");
      if (!button || !panel) return;

      const setExpanded = (expanded) => {
        button.setAttribute("aria-expanded", String(expanded));
        panel.hidden = !expanded;
        faq.classList.toggle("is-open", expanded);
      };

      setExpanded(button.getAttribute("aria-expanded") === "true");
      button.addEventListener("click", () => {
        setExpanded(button.getAttribute("aria-expanded") !== "true");
      });
      faq.dataset.faqReady = "true";
    });
  }

  function initMenu(root = document) {
    const toggle = root.querySelector("[data-menu-toggle]");
    const menu = root.querySelector("[data-site-menu]");
    if (!toggle || !menu) return;

    document.documentElement.classList.add("menu-enhanced");

    const setOpen = (open) => {
      toggle.setAttribute("aria-expanded", String(open));
      menu.classList.toggle("is-open", open);
    };
    toggle.addEventListener("click", () => {
      setOpen(toggle.getAttribute("aria-expanded") !== "true");
    });
    menu.querySelectorAll("a").forEach((link) => {
      link.addEventListener("click", () => setOpen(false));
    });
  }

  function init(root = document) {
    initWhatsAppLinks(root);
    initCarousels(root);
    initCounters(root);
    initFaqs(root);
    initMenu(root);
  }

  window.TBT_UI = Object.freeze({
    init,
    initCarousels,
    initCounters,
    initFaqs,
    buildWhatsAppUrl,
  });

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => init());
  } else {
    init();
  }
})();
