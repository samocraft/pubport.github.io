/**
 * Post overlay — carousel, mobile sheet, touch swipe
 */
function initIgPostOverlay(root) {
  if (!root) return;

  const post = root.classList && root.classList.contains('ig-post')
    ? root
    : root.querySelector('.ig-post');

  if (!post) return;

  const slides = post.querySelectorAll('.ig-post__slide');
  const dots = post.querySelectorAll('.ig-post__dot');
  const prevBtn = post.querySelector('.ig-post__nav--prev');
  const nextBtn = post.querySelector('.ig-post__nav--next');
  const likeBtn = post.querySelector('[data-ig-like]');
  const sheetToggle = post.querySelector('.ig-post__sheet-toggle');
  const mediaEl = post.querySelector('[data-ig-swipe]');
  const total = slides.length;
  let index = 0;

  if (total <= 1) {
    if (prevBtn) prevBtn.hidden = true;
    if (nextBtn) nextBtn.hidden = true;
  }

  function goTo(i) {
    index = (i + total) % total;
    slides.forEach((slide, idx) => slide.classList.toggle('is-active', idx === index));
    dots.forEach((dot, idx) => dot.classList.toggle('is-active', idx === index));
  }

  if (prevBtn) {
    prevBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      goTo(index - 1);
    });
  }

  if (nextBtn) {
    nextBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      goTo(index + 1);
    });
  }

  dots.forEach((dot, idx) => {
    dot.addEventListener('click', (e) => {
      e.stopPropagation();
      goTo(idx);
    });
  });

  post.addEventListener('keydown', (e) => {
    if (total <= 1) return;
    if (e.key === 'ArrowLeft') goTo(index - 1);
    if (e.key === 'ArrowRight') goTo(index + 1);
  });

  if (likeBtn) {
    likeBtn.addEventListener('click', () => {
      likeBtn.classList.toggle('ig-post__action--liked');
      const countEl = post.querySelector('[data-ig-like-count]');
      if (countEl) {
        const n = parseInt(countEl.dataset.base || '0', 10);
        const liked = likeBtn.classList.contains('ig-post__action--liked');
        countEl.textContent = liked
          ? (n + 1).toLocaleString() + ' likes'
          : n.toLocaleString() + ' likes';
      }
    });
  }

  /* Mobile details sheet */
  function setSheetOpen(open) {
    const isMobile = window.matchMedia('(max-width: 900px)').matches;
    if (!isMobile) return;

    post.classList.toggle('is-sheet-open', open);
    if (sheetToggle) {
      sheetToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    }
  }

  if (sheetToggle) {
    sheetToggle.addEventListener('click', () => {
      setSheetOpen(!post.classList.contains('is-sheet-open'));
    });
  }

  /* Touch swipe on media */
  if (mediaEl && total > 1) {
    let startX = 0;
    let startY = 0;
    const threshold = 48;

    mediaEl.addEventListener(
      'touchstart',
      (e) => {
        if (e.touches.length !== 1) return;
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
      },
      { passive: true }
    );

    mediaEl.addEventListener(
      'touchend',
      (e) => {
        if (!e.changedTouches.length) return;
        const dx = e.changedTouches[0].clientX - startX;
        const dy = e.changedTouches[0].clientY - startY;

        if (Math.abs(dx) < threshold || Math.abs(dx) < Math.abs(dy)) return;

        if (dx < 0) goTo(index + 1);
        else goTo(index - 1);
      },
      { passive: true }
    );
  }

  /* Collapse sheet when loading new post on mobile */
  setSheetOpen(false);

  window.addEventListener(
    'resize',
    () => {
      if (!window.matchMedia('(max-width: 900px)').matches) {
        post.classList.remove('is-sheet-open');
        if (sheetToggle) sheetToggle.setAttribute('aria-expanded', 'false');
      }
    },
    { passive: true }
  );
}
