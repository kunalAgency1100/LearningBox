(() => {
  const templateBase = new URL('.', document.currentScript.src);
  const siteBase = new URL('../', templateBase);

  async function loadTemplate(id, filename, initialize) {
    const container = document.getElementById(id);
    if (!container) return;

    try {
      // PHP pages include these templates directly; static pages load them here.
      if (!container.firstElementChild) {
        const response = await fetch(new URL(filename, templateBase));
        if (!response.ok) {
          throw new Error(`Unable to load ${filename}: HTTP ${response.status}`);
        }

        container.innerHTML = await response.text();
      }

      // Keep links and images relative to the site, including on nested pages.
      container.querySelectorAll('[href], [src]').forEach((element) => {
        ['href', 'src'].forEach((attribute) => {
          const value = element.getAttribute(attribute);
          if (value && !/^(?:[a-z][a-z\d+.-]*:|\/|#)/i.test(value)) {
            element.setAttribute(attribute, new URL(value, siteBase).href);
          }
        });
      });

      if (initialize) initialize(container);

      // The footer may arrive after the browser's initial fragment navigation.
      if (window.location.hash === '#contact' && id === 'site-footer') {
        container.querySelector('#contact').scrollIntoView();
      }
    } catch (error) {
      console.error(`Could not load the shared ${id}.`, error);
    }
  }

  function initializeHeader(container) {
    const navbar = container.querySelector('#navbar');
    const hero = document.getElementById('hero');
    const mobileNav = container.querySelector('#mobile-nav');
    const overlay = container.querySelector('#mobile-overlay');
    const openButton = container.querySelector('#mobile-menu-btn');
    const closeButton = container.querySelector('#close-mobile-nav');
    let isOpen = false;
    let previousOverflow = '';
    let overlayTimer;

    function closeMenu() {
      if (!isOpen) return;
      isOpen = false;
      clearTimeout(overlayTimer);
      mobileNav.classList.add('translate-x-full');
      mobileNav.setAttribute('inert', '');
      mobileNav.setAttribute('aria-hidden', 'true');
      overlay.classList.add('opacity-0', 'pointer-events-none');
      overlayTimer = setTimeout(() => overlay.classList.add('hidden'), 300);
      openButton.setAttribute('aria-expanded', 'false');
      document.body.style.overflow = previousOverflow;
      openButton.focus();
    }

    openButton.addEventListener('click', () => {
      if (isOpen) {
        closeMenu();
        return;
      }
      isOpen = true;
      clearTimeout(overlayTimer);
      previousOverflow = document.body.style.overflow;
      mobileNav.classList.remove('translate-x-full');
      mobileNav.removeAttribute('inert');
      mobileNav.setAttribute('aria-hidden', 'false');
      overlay.classList.remove('hidden', 'pointer-events-none');
      overlayTimer = setTimeout(() => overlay.classList.remove('opacity-0'), 10);
      openButton.setAttribute('aria-expanded', 'true');
      document.body.style.overflow = 'hidden';
      closeButton.focus();
    });

    closeButton.addEventListener('click', closeMenu);
    overlay.addEventListener('click', closeMenu);
    mobileNav.querySelectorAll('a').forEach((link) => {
      link.addEventListener('click', closeMenu);
    });
    document.addEventListener('keydown', (event) => {
      if (!isOpen) return;
      if (event.key === 'Escape') closeMenu();
      if (event.key === 'Tab') {
        const items = mobileNav.querySelectorAll('button, a[href]');
        const first = items[0];
        const last = items[items.length - 1];
        if (event.shiftKey && document.activeElement === first) {
          event.preventDefault();
          last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
          event.preventDefault();
          first.focus();
        }
      }
    });

    const desktop = window.matchMedia('(min-width: 1024px)');
    desktop.addEventListener('change', (event) => {
      if (event.matches) closeMenu();
    });

    const currentPath = window.location.pathname;
    container.querySelectorAll('a[href]').forEach((link) => {
      if (new URL(link.href).pathname === currentPath) {
        link.setAttribute('aria-current', 'page');
      }
    });

    function updateNavbar() {
      const opacity = hero
        ? Math.min(Math.max(window.scrollY / Math.max(hero.offsetHeight - 80, 1), 0), 1)
        : 1;
      navbar.style.backgroundColor = `rgba(0, 9, 118, ${opacity})`;
      navbar.style.boxShadow = window.scrollY > 50 ? '0 10px 15px rgba(0,0,0,0.2)' : 'none';
    }

    navbar.style.transition = 'background-color 0.4s ease, box-shadow 0.4s ease';
    window.addEventListener('scroll', updateNavbar, { passive: true });
    window.addEventListener('resize', updateNavbar);
    updateNavbar();
  }

  loadTemplate('site-header', 'header.html', initializeHeader);
  loadTemplate('site-footer', 'footer.html');
})();
