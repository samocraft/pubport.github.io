/**
 * Multi-step project upload UI
 */
(function () {
  const form = document.getElementById('upload-project-form');
  if (!form) return;

  const panels = [...form.querySelectorAll('[data-upload-panel]')];
  const steps = [...document.querySelectorAll('[data-upload-step]')];
  const btnPrev = form.querySelector('[data-upload-prev]');
  const btnNext = form.querySelector('[data-upload-next]');
  const btnSubmit = form.querySelector('[data-upload-submit]');
  let current = 0;

  const thumbInput = form.querySelector('#thumbnail');
  const imagesInput = form.querySelector('#images');
  const videosInput = form.querySelector('#videos');
  const thumbPreview = form.querySelector('[data-thumb-preview]');
  const imagesPreview = form.querySelector('[data-images-preview]');
  const videosPreview = form.querySelector('[data-videos-preview]');

  /** @type {File[]} */
  let imageFiles = [];
  /** @type {File[]} */
  let videoFiles = [];

  function showStep(index) {
    current = Math.max(0, Math.min(index, panels.length - 1));
    panels.forEach((p, i) => p.classList.toggle('is-active', i === current));
    steps.forEach((s, i) => {
      s.classList.toggle('is-active', i === current);
      s.classList.toggle('is-complete', i < current);
    });
    btnPrev.hidden = current === 0;
    btnNext.hidden = current === panels.length - 1;
    btnSubmit.hidden = current !== panels.length - 1;
    if (current === panels.length - 1) fillReview();
  }

  function validateStep(index) {
    if (index === 0) {
      const title = form.querySelector('#title');
      const category = form.querySelector('input[name="category"]:checked');
      if (!title?.value.trim()) {
        title?.focus();
        return 'Enter a project title.';
      }
      if (!category) return 'Select a category.';
    }
    if (index === 1) {
      if (!thumbInput?.files?.length) return 'Choose a thumbnail image.';
    }
    if (index === 2) {
      if (imageFiles.length === 0 && videoFiles.length === 0) {
        return 'Add at least one gallery image or video.';
      }
    }
    return null;
  }

  btnNext?.addEventListener('click', () => {
    const err = validateStep(current);
    if (err) {
      alert(err);
      return;
    }
    showStep(current + 1);
  });

  btnPrev?.addEventListener('click', () => showStep(current - 1));

  function setupDropzone(zone) {
    const input = zone.querySelector('input[type="file"]');
    if (!input) return;

    ['dragenter', 'dragover'].forEach((ev) => {
      zone.addEventListener(ev, (e) => {
        e.preventDefault();
        zone.classList.add('is-dragover');
      });
    });
    ['dragleave', 'drop'].forEach((ev) => {
      zone.addEventListener(ev, (e) => {
        e.preventDefault();
        zone.classList.remove('is-dragover');
      });
    });
  }

  form.querySelectorAll('.upload-drop').forEach(setupDropzone);

  thumbInput?.addEventListener('change', () => {
    const file = thumbInput.files?.[0];
    if (!file || !thumbPreview) return;
    const img = thumbPreview.querySelector('img') || document.createElement('img');
    img.src = URL.createObjectURL(file);
    if (!thumbPreview.querySelector('img')) thumbPreview.appendChild(img);
    thumbPreview.classList.add('has-image');
  });

  function renderPreviews(container, files, type, onRemove) {
    if (!container) return;
    container.innerHTML = '';
    files.forEach((file, index) => {
      const wrap = document.createElement('div');
      wrap.className = 'upload-preview';
      const url = URL.createObjectURL(file);
      if (type === 'video') {
        const vid = document.createElement('video');
        vid.src = url;
        vid.muted = true;
        wrap.appendChild(vid);
      } else {
        const img = document.createElement('img');
        img.src = url;
        img.alt = file.name;
        wrap.appendChild(img);
      }
      const badge = document.createElement('span');
      badge.className = 'upload-preview__badge';
      badge.textContent = type;
      wrap.appendChild(badge);
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'upload-preview__remove';
      btn.innerHTML = '&times;';
      btn.setAttribute('aria-label', 'Remove');
      btn.addEventListener('click', () => onRemove(index));
      wrap.appendChild(btn);
      container.appendChild(wrap);
    });
  }

  function syncFileInput(input, files) {
    if (!input) return;
    const dt = new DataTransfer();
    files.forEach((f) => dt.items.add(f));
    input.files = dt.files;
  }

  function refreshImagePreviews() {
    renderPreviews(imagesPreview, imageFiles, 'image', (i) => {
      imageFiles.splice(i, 1);
      syncFileInput(imagesInput, imageFiles);
      refreshImagePreviews();
    });
  }

  function refreshVideoPreviews() {
    renderPreviews(videosPreview, videoFiles, 'video', (i) => {
      videoFiles.splice(i, 1);
      syncFileInput(videosInput, videoFiles);
      refreshVideoPreviews();
    });
  }

  imagesInput?.addEventListener('change', () => {
    imageFiles = imageFiles.concat([...(imagesInput.files || [])]);
    syncFileInput(imagesInput, imageFiles);
    refreshImagePreviews();
    imagesInput.value = '';
  });

  videosInput?.addEventListener('change', () => {
    videoFiles = videoFiles.concat([...(videosInput.files || [])]);
    syncFileInput(videosInput, videoFiles);
    refreshVideoPreviews();
    videosInput.value = '';
  });

  function fillReview() {
    const title = form.querySelector('#title')?.value || '—';
    const subtitle = form.querySelector('#subtitle')?.value || '—';
    const cat = form.querySelector('input[name="category"]:checked');
    const catLabel = cat?.closest('.upload-category')?.querySelector('.upload-category__card')?.textContent?.trim() || '—';
    const set = (sel, val) => {
      const el = form.querySelector(sel);
      if (el) el.textContent = val;
    };
    set('[data-review-title]', title);
    set('[data-review-subtitle]', subtitle || 'None');
    set('[data-review-category]', catLabel);
    set('[data-review-thumb]', thumbInput?.files?.[0]?.name || '—');
    set('[data-review-media]', `${imageFiles.length} image(s), ${videoFiles.length} video(s)`);
  }

  form.addEventListener('submit', (e) => {
    const err = validateStep(2) || validateStep(1) || validateStep(0);
    if (err) {
      e.preventDefault();
      alert(err);
      return;
    }
    syncFileInput(imagesInput, imageFiles);
    syncFileInput(videosInput, videoFiles);
    form.classList.add('is-submitting');
  });

  showStep(0);
})();
