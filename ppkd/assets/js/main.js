// ============================================================
// Main JavaScript — Ikatan Alumni PPKD
// ============================================================

document.addEventListener('DOMContentLoaded', function () {

  // ---- Init Lucide Icons ----
  if (window.lucide) lucide.createIcons();

  // ---- Navbar scroll effect ----
  const header = document.getElementById('siteHeader');
  function handleScroll() {
    if (header) {
      header.classList.toggle('scrolled', window.scrollY > 60);
    }
  }
  window.addEventListener('scroll', handleScroll, { passive: true });
  handleScroll();

  // ---- Mobile nav ----
  const toggler   = document.getElementById('navToggler');
  const mobileNav = document.getElementById('mobileNav');
  const closeBtn  = document.getElementById('mobileNavClose');

  function openMobileNav() {
    if (mobileNav) {
      mobileNav.classList.add('open');
      document.body.style.overflow = 'hidden';
    }
  }

  function closeMobileNav() {
    if (mobileNav) {
      mobileNav.classList.remove('open');
      document.body.style.overflow = '';
    }
  }

  if (toggler) toggler.addEventListener('click', openMobileNav);
  if (closeBtn) closeBtn.addEventListener('click', closeMobileNav);

  // Close mobile nav on outside click
  document.addEventListener('click', function (e) {
    if (mobileNav && mobileNav.classList.contains('open')) {
      if (!mobileNav.contains(e.target) && e.target !== toggler && !toggler.contains(e.target)) {
        closeMobileNav();
      }
    }
  });

  // ---- Counter animation ----
  function animateCounter(el, target, duration = 1800) {
    const startTime = performance.now();
    const start = 0;

    function update(currentTime) {
      const elapsed  = currentTime - startTime;
      const progress = Math.min(elapsed / duration, 1);
      // Ease out cubic
      const eased = 1 - Math.pow(1 - progress, 3);
      const current = Math.round(start + (target - start) * eased);
      el.textContent = current.toLocaleString('id-ID');
      if (progress < 1) requestAnimationFrame(update);
    }
    requestAnimationFrame(update);
  }

  // Intersection Observer for counters
  const counters = document.querySelectorAll('.counter[data-target]');
  if (counters.length) {
    const observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          const target = parseInt(entry.target.dataset.target, 10);
          animateCounter(entry.target, target);
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.5 });

    counters.forEach(c => observer.observe(c));
  }

  // ---- Smooth scroll for anchor links ----
  document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
    anchor.addEventListener('click', function (e) {
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        e.preventDefault();
        const offset = 80;
        const top = target.getBoundingClientRect().top + window.scrollY - offset;
        window.scrollTo({ top, behavior: 'smooth' });
      }
    });
  });

  // ---- Auto-dismiss flash alerts ----
  const flashAlerts = document.querySelectorAll('.alert:not(.alert-static)');
  flashAlerts.forEach(function (alert) {
    setTimeout(function () {
      if (alert.parentElement) {
        alert.style.opacity = '0';
        alert.style.transition = 'opacity 0.4s ease';
        setTimeout(function () { alert.remove(); }, 400);
      }
    }, 5000);
  });

  // ---- Image error fallback ----
  document.querySelectorAll('img[data-fallback]').forEach(function (img) {
    img.addEventListener('error', function () {
      this.src = this.dataset.fallback;
    });
  });

});
