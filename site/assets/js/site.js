(function () {
  "use strict";

  const reducedMotionQuery = window.matchMedia(
    "(prefers-reduced-motion: reduce)"
  );

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
    const formatted = Number(value).toFixed(counter.decimals);
    return `${counter.prefix || ""}${formatted}${counter.suffix || ""}`;
  }

  function parseCounter(element) {
    const finalText = element.textContent.trim();
    const match = finalText.match(/^(.*?)(-?\d+(?:\.\d+)?)([^\d]*)$/);
    if (!match) return null;
    const decimalPart = match[2].split(".")[1] || "";
    return {
      value: Number(match[2]),
      prefix: match[1],
      suffix: match[3],
      decimals: decimalPart.length,
      finalText,
    };
  }

  function initCounters(root = document) {
    const elements = Array.from(root.querySelectorAll("[data-counter-key]"));
    const configuredCounters = new Map(
      elements.map((element) => [element, parseCounter(element)])
    );
    const showFinal = (element) => {
      const counter = configuredCounters.get(element);
      if (counter) element.textContent = counter.finalText;
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
          const counter = configuredCounters.get(element);
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

  function initNavigation(root = document) {
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

    menu.querySelectorAll("[data-submenu-toggle]").forEach((submenuToggle) => {
      const submenuId = submenuToggle.getAttribute("aria-controls");
      const submenu = submenuId ? root.getElementById(submenuId) : null;
      if (!submenu) return;

      const setSubmenuOpen = (open) => {
        submenuToggle.setAttribute("aria-expanded", String(open));
        submenuToggle.setAttribute(
          "aria-label",
          `${open ? "Hide" : "Show"} Classes submenu`
        );
        submenu.hidden = !open;
      };

      setSubmenuOpen(false);
      submenuToggle.addEventListener("click", () => {
        setSubmenuOpen(submenuToggle.getAttribute("aria-expanded") !== "true");
      });
      submenu.addEventListener("keydown", (event) => {
        if (event.key !== "Escape") return;
        setSubmenuOpen(false);
        submenuToggle.focus();
      });
    });

    menu.querySelectorAll("a").forEach((link) => {
      link.addEventListener("click", () => setOpen(false));
    });
  }

  function initTestimonialStories(root = document) {
    const source = root.querySelector("[data-testimonial-source]");
    if (!source || source.dataset.storiesReady === "true") return;

    const testimonials = Array.from(
      source.querySelectorAll(".testimonial--archive")
    );
    const storyFrames = [];
    const frameReset =
      "<style>html,body{width:100%;height:100%;margin:0;overflow:hidden}" +
      "body{background:#111}a{box-sizing:border-box}img{display:block}</style>";
    const groups = [
      {
        year: "2025",
        start: 0,
        ids: [
          "RJxR3ONbylQ", "EfS7RQit79U", "STE5tyEo10w", "UE2K7-92pkM",
          "nCfeU3UthgA", "MY7T5Hqf8l4", "cDz1UQe4wOI",
        ],
      },
      {
        year: "2024",
        start: 16,
        ids: [
          "V4dj5XYEcow", "shZaStH4nGI", "xG8PcBOOhCA", "XuuBFtldw_A",
          "xT8OzWLZ4w8", "uewzV0bhqoc", "YNEW5TFVyMs", "H8YSD2u4tjc",
          "QzfoV5yH1Tc", "jH3ve35ECXE", "bTQfAjTy0_E", "Ycvn6yK_F0o",
          "-YGN_dQE3qM", "Fxa8-Hi2dfQ", "BZeEXmU8oQQ", "hqZtkbQNxxc",
        ],
      },
      {
        year: "2023",
        start: 81,
        ids: [
          "3V8xUj2mKyI", "kEb-tGNNixE", "nmng0-6Li5A", "thLdbLZx0so",
          "mzWs-4agDjY", "QldLg8X7qAE", "zioHbIDys0w", "ADnbKkV9Isc",
          "nYeFv7a5L78", "WL8_ziqlq7s", "dKZxiqBVXTc", "wXiYDwwdDgM",
          "W1WnUFygLhA", "Cej5PUA-ICE", "f3dfLcZ7XqY", "PmAzr0ycX38",
        ],
      },
      {
        year: "2022",
        start: 107,
        ids: [
          "2qbdprvxXh0", "E7-rp_p1CVg", "SWHGmES4VNI", "ay7w4ZZy3ec",
          "IXXuN2_fkTw", "jfE_9MYrfgw", "_YGWDe-MLTs", "AQwdcGSjsOI",
          "83pqomh2rwE", "zq7KbeHpKYc", "R1WUxV4M9gs", "dXqkbuFYkLU",
          "Z9benoNGU-E", "O0-c-82JCes", "bzAEu4MzPoU", "oVZTlaOjyRI",
          "nc_LW_e8tA0", "qv8sd3ciVGc", "lU1ZIckiN20",
        ],
      },
      {
        year: "2021",
        start: 129,
        ids: ["2guZtb86pN0", "rcnCudMPW8I", "JD-q5xPFmXY"],
      },
    ];

    groups.forEach((group) => {
      const list = root.querySelector(`[data-story-list="${group.year}"]`);
      if (!list) return;
      group.ids.forEach((videoId, offset) => {
        const story = testimonials[group.start + offset];
        if (!story) return;
        const quote = story.querySelector(".testimonial__quote");
        const person = story.querySelector(".testimonial__person strong");
        const video = document.createElement("div");
        const iframe = document.createElement("iframe");

        story.classList.add("testimonial-story");
        quote?.classList.add("testimonial-story__quote");
        video.className = "testimonial-story__video";
        iframe.src = `https://www.youtube.com/embed/${videoId}?rel=0`;
        iframe.width = "694";
        iframe.height = "390";
        iframe.loading = "lazy";
        iframe.scrolling = "no";
        iframe.title = `${person?.textContent.trim() || "Student"} story`;
        iframe.srcdoc =
          frameReset +
          `<a href="https://www.youtube.com/embed/${videoId}?rel=0" ` +
          'style="display:flex;width:100%;height:100%;align-items:center;' +
          'justify-content:center;gap:14px;background:#111;color:#fff;' +
          'font:700 20px Arial,sans-serif;text-decoration:none">' +
          '<span style="display:grid;width:72px;height:50px;place-items:center;' +
          'border-radius:14px;background:#f00;font-size:28px">▶</span>' +
          '<span>Watch student story</span></a>';
        iframe.dataset.videoId = videoId;
        iframe.referrerPolicy = "strict-origin-when-cross-origin";
        iframe.allow =
          "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share";
        iframe.allowFullscreen = true;
        video.append(iframe);
        storyFrames.push(iframe);
        story.prepend(video);
        list.append(story);
      });
    });

    const loadPoster = (iframe) => {
      const videoId = iframe.dataset.videoId;
      if (!videoId || iframe.dataset.posterReady === "true") return;
      iframe.srcdoc =
        frameReset +
        `<a href="https://www.youtube.com/embed/${videoId}?rel=0" ` +
        'style="position:relative;display:block;width:100%;height:100%;' +
        'overflow:hidden;background:#111;color:#fff;text-decoration:none">' +
        `<img src="https://i.ytimg.com/vi/${videoId}/hqdefault.jpg" ` +
        'alt="" style="width:100%;height:100%;object-fit:cover">' +
        '<span style="position:absolute;inset:0;display:grid;place-items:center">' +
        '<span style="display:grid;width:72px;height:50px;place-items:center;' +
        'border-radius:14px;background:#f00;color:#fff;font:700 28px Arial">▶' +
        '</span></span></a>';
      iframe.dataset.posterReady = "true";
    };

    if ("IntersectionObserver" in window) {
      const posterObserver = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            loadPoster(entry.target);
            posterObserver.unobserve(entry.target);
          });
        },
        { rootMargin: "600px 0px" }
      );
      storyFrames.forEach((iframe) => posterObserver.observe(iframe));
    } else {
      storyFrames.forEach(loadPoster);
    }

    source.hidden = false;
    source.dataset.storiesReady = "true";
  }

  function init(root = document) {
    initCarousels(root);
    initCounters(root);
    initFaqs(root);
    initNavigation(root);
    initTestimonialStories(root);
  }

  window.TBT_UI = Object.freeze({
    init,
    initCarousels,
    initCounters,
    initFaqs,
    initNavigation,
    initTestimonialStories,
  });

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => init());
  } else {
    init();
  }
})();
