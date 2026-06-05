/**
 * Portfolio grid — filters & project overlay
 */
(function () {
  const wrap = document.querySelector('.portfolio-wrap');
  if (!wrap) return;

  const modaloverContainer = document.getElementById('modaloverContainer');
  const overlaypage = document.getElementById('overlaypage');
  const closeBtn = document.getElementById('closemodalover');
  const modalContent = document.querySelector('.testimonials-modalover .modalover-content');
  const projectItems = wrap.querySelectorAll('.project-item');
  const emptyEl = wrap.querySelector('[data-portfolio-empty]');
  const filterButtons = wrap.querySelectorAll('[data-filter]');
  const selectBox = wrap.querySelector('[data-select]');
  const selectValue = wrap.querySelector('.select-value');

  function applyFilter(category) {
    const cat = category.toLowerCase();
    let visible = 0;

    projectItems.forEach((item, i) => {
      const itemCat = (item.getAttribute('data-category') || '').toLowerCase();
      const show = cat === 'all' || itemCat === cat;
      item.classList.toggle('is-visible', show);
      if (show) {
        item.style.setProperty('--pf-stagger', `${Math.min(i * 0.05, 0.35)}s`);
        visible++;
      }
    });

    if (emptyEl) {
      emptyEl.hidden = visible > 0;
      emptyEl.classList.toggle('is-shown', visible === 0);
    }
  }

  function setActiveFilter(category) {
    filterButtons.forEach((btn) => {
      const match = btn.getAttribute('data-filter') === category;
      btn.classList.toggle('is-active', match);
      if (btn.classList.contains('portfolio-filter')) {
        btn.classList.toggle('active', match);
      }
    });
    if (selectValue) {
      const active = Array.from(wrap.querySelectorAll('[data-filter]')).find(
        (el) => el.getAttribute('data-filter') === category
      );
      if (active) selectValue.textContent = active.textContent.trim();
    }

    wrap.querySelectorAll('.portfolio-filter[role="tab"]').forEach((tab) => {
      tab.setAttribute('aria-selected', tab.getAttribute('data-filter') === category ? 'true' : 'false');
    });

    const activeChip = wrap.querySelector('.portfolio-filter.is-active');
    if (activeChip && wrap.classList.contains('is-mobile')) {
      activeChip.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
    }
  }

  filterButtons.forEach((button) => {
    button.addEventListener('click', () => {
      const category = button.getAttribute('data-filter') || 'all';
      applyFilter(category);
      setActiveFilter(category);
      if (selectBox) selectBox.classList.remove('active');
    });
  });

  if (selectBox) {
    selectBox.addEventListener('click', (e) => {
      e.stopPropagation();
      selectBox.classList.toggle('active');
    });
    document.addEventListener('click', (e) => {
      if (!selectBox.contains(e.target)) selectBox.classList.remove('active');
    });
  }

  function closeModal() {
    modaloverContainer?.classList.remove('active');
    overlaypage?.classList.remove('active');
    document.body.style.overflow = '';
    if (modalContent) modalContent.innerHTML = '';
  }

  function openModal() {
    modaloverContainer?.classList.add('active');
    overlaypage?.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function loadProject(projectId) {
    if (!modalContent || !projectId) return;

    fetch('overlay.php?id=' + encodeURIComponent(projectId))
      .then((r) => r.text())
      .then((html) => {
        modalContent.innerHTML = html;
        openModal();
        if (typeof initIgPostOverlay === 'function') {
          initIgPostOverlay(modalContent);
        }
      })
      .catch((err) => console.error('Error loading project:', err));
  }

  wrap.addEventListener('click', (e) => {
    const link = e.target.closest('.open-overlay');
    if (!link) return;
    e.preventDefault();
    loadProject(link.getAttribute('data-project-id'));
  });

  closeBtn?.addEventListener('click', closeModal);
  overlaypage?.addEventListener('click', closeModal);

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modaloverContainer?.classList.contains('active')) {
      closeModal();
    }
  });

  const mqMobile = window.matchMedia('(max-width: 767px)');
  if (mqMobile.matches) {
    wrap.classList.add('is-mobile');
  }
  mqMobile.addEventListener('change', (e) => {
    wrap.classList.toggle('is-mobile', e.matches);
  });

  applyFilter('all');
  setActiveFilter('all');
})();
