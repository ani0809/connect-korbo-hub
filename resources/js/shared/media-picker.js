const SELECTOR_TRIGGER = '[data-toggle="media-picker"]';

function csrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function scopePrefix() {
  const p = window.location.pathname || '';
  if (p.startsWith('/admin')) return '/admin/media-library';
  if (p.startsWith('/seller')) return '/seller/media-library';
  return '/account/media-library';
}

function endpoints() {
  const base = scopePrefix();
  return {
    list: `${base}/list`,
    upload: `${base}/upload`,
    resolve: `${base}/resolve`,
    update: `${base}/update`,
  };
}

function normalizeIds(value) {
  if (!value) return [];
  if (Array.isArray(value)) return value.map((v) => Number(v)).filter(Boolean);
  return String(value).split(',').map((v) => Number(v.trim())).filter(Boolean);
}

function mediaThumb(item) {
  if (item.type === 'video') return `<video class="shopadmin-editor-media-thumb" src="${item.thumbnail_url || item.url}" muted></video>`;
  return `<img class="shopadmin-editor-media-thumb" src="${item.thumbnail_url || item.url}" alt="${item.title || item.name || 'media'}" />`;
}

function formatBytes(size = 0) {
  if (size <= 0) return '0 B';
  const units = ['B', 'KB', 'MB', 'GB'];
  let value = Number(size);
  let idx = 0;
  while (value >= 1024 && idx < units.length - 1) {
    value /= 1024;
    idx += 1;
  }
  return `${value.toFixed(value >= 10 || idx === 0 ? 0 : 1)} ${units[idx]}`;
}

function formatDateTimeDMY(value) {
  if (!value) return '';
  const dt = new Date(value);
  if (Number.isNaN(dt.getTime())) return String(value);
  const day = dt.getDate();
  const month = dt.getMonth() + 1;
  const year = dt.getFullYear();
  let hour = dt.getHours();
  const minute = String(dt.getMinutes()).padStart(2, '0');
  const ampm = hour >= 12 ? 'PM' : 'AM';
  hour = hour % 12 || 12;
  return `${day}/${month}/${year} ${hour}:${minute} ${ampm}`;
}

async function requestJson(url, options = {}) {
  const res = await fetch(url, {
    credentials: 'same-origin',
    headers: {
      Accept: 'application/json',
      'X-CSRF-TOKEN': csrfToken(),
      ...(options.headers || {}),
    },
    ...options,
  });
  if (!res.ok) throw new Error(`Request failed (${res.status})`);
  return res.json();
}

function parseFilters(modalEl) {
  const type = modalEl.querySelector('.js-wp-filter-type')?.value || 'all';
  const date = modalEl.querySelector('.js-wp-filter-date')?.value || 'all';
  const q = modalEl.querySelector('.js-editor-media-search')?.value || '';
  return { type, date, q };
}

async function loadMedia(modalEl) {
  const ep = endpoints();
  const { type, q } = parseFilters(modalEl);
  const params = new URLSearchParams();
  if (type && type !== 'all') params.set('type', type);
  if (q) params.set('q', q);
  params.set('per_page', '120');
  const payload = await requestJson(`${ep.list}?${params.toString()}`);
  return Array.isArray(payload?.data) ? payload.data : [];
}

function createModalHtml({ modalTitle = 'Media Manager', actionLabel = 'Insert selected' } = {}) {
  const id = `shopadmin-global-media-modal-${Date.now()}`;
  return `
    <div class="modal fade shopadmin-editor-media-modal" id="${id}" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
          <div class="modal-header wpmm-header">
            <h5 class="modal-title">${modalTitle}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          </div>
          <div class="wpmm-tabs">
            <button type="button" class="wpmm-tab is-active" data-tab="library">Media Library</button>
            <button type="button" class="wpmm-tab" data-tab="upload">Upload Files</button>
          </div>
          <div class="modal-body wpmm-body">
            <section class="wpmm-pane is-active" data-pane="library">
              <div class="wpmm-toolbar">
                <div class="wpmm-toolbar-left">
                  <select class="form-control form-control-sm js-wp-filter-type">
                    <option value="all">All media items</option>
                    <option value="image">Images</option>
                    <option value="video">Videos</option>
                  </select>
                  <select class="form-control form-control-sm js-wp-filter-date">
                    <option value="all">All dates</option>
                  </select>
                </div>
                <div class="wpmm-toolbar-right">
                  <label class="wpmm-search-label">Search media:</label>
                  <input type="text" class="form-control form-control-sm js-editor-media-search" />
                </div>
              </div>
              <div class="wpmm-library-layout">
                <div class="wpmm-grid-wrap">
                  <div class="shopadmin-editor-media-grid js-editor-media-grid"></div>
                  <div class="shopadmin-editor-media-empty js-editor-media-empty d-none">No media found.</div>
                </div>
                <aside class="wpmm-details js-wpmm-details">
                  <div class="wpmm-details-empty">Select a file to view details</div>
                </aside>
              </div>
            </section>
            <section class="wpmm-pane" data-pane="upload">
              <div class="wpmm-upload-dropzone js-wpmm-dropzone">
                <p class="mb-1">Drop files to upload</p>
                <p class="wpmm-or">or</p>
                <button type="button" class="btn btn-sm btn-secondary js-wpmm-select-files">Select Files</button>
                <input type="file" class="form-control-file js-editor-media-upload d-none" accept="image/*,video/*" multiple />
                <div class="wpmm-upload-progress js-wpmm-upload-progress" hidden>
                  <div class="wpmm-upload-progress-bar js-wpmm-upload-progress-bar"></div>
                </div>
                <p class="wpmm-upload-status js-wpmm-upload-status" hidden></p>
                <p class="wpmm-max">Maximum upload file size: 25 MB.</p>
              </div>
            </section>
          </div>
          <div class="modal-footer wpmm-footer">
            <div class="wpmm-footer-meta js-wpmm-footer-meta">No items selected</div>
            <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary js-editor-media-insert" disabled>${actionLabel}</button>
          </div>
        </div>
      </div>
    </div>`;
}

function renderDetailsPanel(container, item, multiple = false, selectedCount = 0) {
  if (!container) return;
  if (!item) {
    container.innerHTML = '<div class="wpmm-details-empty">Select a file to view details</div>';
    return;
  }
  const preview = item.type === 'video'
    ? `<video class="wpmm-details-preview" src="${item.url}" controls preload="metadata"></video>`
    : `<img class="wpmm-details-preview" src="${item.url}" alt="${item.title || item.name || ''}" />`;
  const uploadedOn = item.created_at ? formatDateTimeDMY(item.created_at) : '';
  const canShowDimensions = item.type === 'image';
  const formatDimensions = (w, h) => `${w} x ${h} px`;
  container.innerHTML = `
    <div class="wpmm-details-inner">
      <div class="wpmm-details-title">ATTACHMENT DETAILS</div>
      ${preview}
      <div class="wpmm-details-meta">
        <div><strong>File name:</strong> ${item.name || ''}</div>
        <div><strong>File type:</strong> ${item.mime || item.type || ''}</div>
        <div><strong>File size:</strong> ${formatBytes(Number(item.size || 0))}</div>
        <div><strong>Dimensions:</strong> <span class="js-wpmm-dimensions">${canShowDimensions ? 'Loading...' : '-'}</span></div>
        <div><strong>Uploaded on:</strong> ${uploadedOn}</div>
      </div>
      <label class="wpmm-field-label mt-2">Title</label>
      <input type="text" class="form-control form-control-sm js-wpmm-detail-title" value="${item.title || ''}" />
      <label class="wpmm-field-label mt-2">Alt Text</label>
      <input type="text" class="form-control form-control-sm js-wpmm-detail-alt" value="${item.alt_text || ''}" />
      <label class="wpmm-field-label mt-2">File URL</label>
      <input type="text" class="form-control form-control-sm js-wpmm-file-url" readonly value="${item.url || ''}" />
      <button type="button" class="btn btn-sm btn-outline-primary mt-2 js-wpmm-copy-url">Copy URL</button>
      <div class="wpmm-save-hint js-wpmm-save-hint"></div>
      ${multiple ? `<div class="wpmm-selected-count mt-2">${selectedCount} selected</div>` : ''}
    </div>
  `;

  if (!canShowDimensions) return;
  const dimensionsEl = container.querySelector('.js-wpmm-dimensions');
  if (!dimensionsEl) return;
  const width = Number(item.width || 0);
  const height = Number(item.height || 0);
  if (width > 0 && height > 0) {
    dimensionsEl.textContent = formatDimensions(width, height);
    return;
  }
  const probe = new Image();
  probe.onload = () => {
    const w = Number(probe.naturalWidth || 0);
    const h = Number(probe.naturalHeight || 0);
    dimensionsEl.textContent = (w > 0 && h > 0) ? formatDimensions(w, h) : '-';
  };
  probe.onerror = () => {
    dimensionsEl.textContent = '-';
  };
  probe.src = item.url || '';
}

function updateActionText(button, multiple = false, actionLabel = '') {
  if (!button) return;
  if (actionLabel) {
    button.textContent = actionLabel;
    return;
  }
  button.textContent = multiple ? 'Add to gallery' : 'Set product image';
}

function updateFooterMeta(el, selectedCount, multiple = false) {
  if (!el) return;
  if (!selectedCount) {
    el.textContent = 'No items selected';
    return;
  }
  if (multiple) {
    el.textContent = `${selectedCount} item${selectedCount > 1 ? 's' : ''} selected`;
    return;
  }
  el.textContent = '1 item selected';
}

function activatePane(modalEl, pane) {
  modalEl.querySelectorAll('.wpmm-tab').forEach((tab) => tab.classList.toggle('is-active', tab.getAttribute('data-tab') === pane));
  modalEl.querySelectorAll('.wpmm-pane').forEach((el) => el.classList.toggle('is-active', el.getAttribute('data-pane') === pane));
}

function formatMonthYear(value) {
  if (!value) return '';
  const date = new Date(`${value}-01T00:00:00`);
  if (Number.isNaN(date.getTime())) return value;
  return date.toLocaleString(undefined, { month: 'long', year: 'numeric' });
}

function monthKeyFromItem(item) {
  if (!item?.created_at) return '';
  const d = new Date(item.created_at);
  if (Number.isNaN(d.getTime())) return '';
  const month = String(d.getMonth() + 1).padStart(2, '0');
  return `${d.getFullYear()}-${month}`;
}

function refreshDateFilter(dateSelect, list) {
  if (!dateSelect) return;
  const current = dateSelect.value || 'all';
  const months = Array.from(new Set((list || []).map((item) => monthKeyFromItem(item)).filter(Boolean))).sort().reverse();
  dateSelect.innerHTML = '<option value="all">All dates</option>';
  months.forEach((month) => {
    const option = document.createElement('option');
    option.value = month;
    option.textContent = formatMonthYear(month);
    dateSelect.appendChild(option);
  });
  dateSelect.disabled = months.length === 0;
  if (months.includes(current)) {
    dateSelect.value = current;
  } else {
    dateSelect.value = 'all';
  }
}

function renderFieldPreview(container, items, multiple, onRemove) {
  if (!container) return;
  const isThumbPreview = container.id === 'thumbnailMediaPreview';
  const isGalleryPreview = container.id === 'galleryMediaPreview';
  const thumbTrigger = isThumbPreview
    ? document.querySelector('[data-input="#thumbnail_media_id"][data-toggle="media-picker"]')
    : null;
  container.innerHTML = '';
  if (!items.length) {
    if (thumbTrigger) thumbTrigger.style.display = '';
    container.innerHTML = isThumbPreview || isGalleryPreview ? '' : '<div class="shopadmin-editor-media-empty">No media selected.</div>';
    return;
  }
  if (isThumbPreview) {
    if (thumbTrigger) thumbTrigger.style.display = 'none';
    const item = items[0];
    const card = document.createElement('div');
    card.className = 'zpf-wp-image-card';
    if (item?.id) card.setAttribute('data-media-id', String(item.id));
    card.innerHTML = `
      <img class="zpf-wp-image-main" src="${item.thumbnail_url || item.url}" alt="${item.title || item.name || 'Product image'}" />
      <p class="zpf-wp-help">Click the image to edit or update</p>
      <button type="button" class="zpf-wp-remove-link">Remove product image</button>
      <button type="button" class="zpf-wp-video-btn">+ Video</button>
    `;
    card.querySelector('.zpf-wp-remove-link')?.addEventListener('click', () => onRemove(0));
    container.appendChild(card);
    return;
  }

  if (isGalleryPreview) {
    items.forEach((item, idx) => {
      const card = document.createElement('div');
      card.className = 'zpf-wp-gallery-item';
      if (item?.id) card.setAttribute('data-media-id', String(item.id));
      card.innerHTML = `<img class="zpf-wp-gallery-thumb" src="${item.thumbnail_url || item.url}" alt="${item.title || item.name || 'Gallery image'}" />`;
      card.addEventListener('dblclick', () => onRemove(idx));
      container.appendChild(card);
    });
    return;
  }

  items.forEach((item, idx) => {
    const card = document.createElement('div');
    card.className = 'shopadmin-editor-media-item is-selected';
    if (item?.id) {
      card.setAttribute('data-media-id', String(item.id));
    }
    card.innerHTML = `${mediaThumb(item)}<div class="shopadmin-editor-media-meta">${item.name || ''}</div>`;
    const rm = document.createElement('button');
    rm.type = 'button';
    rm.className = 'btn btn-xs btn-light mt-1';
    rm.textContent = multiple ? 'Remove' : 'Replace';
    rm.addEventListener('click', () => onRemove(idx));
    card.appendChild(rm);
    container.appendChild(card);
  });
}

async function resolveMediaIds(ids) {
  if (!ids.length) return [];
  const ep = endpoints();
  const payload = await requestJson(ep.resolve, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ ids }),
  });
  return Array.isArray(payload?.data) ? payload.data : [];
}

async function uploadMediaFile(file, { onProgress } = {}) {
  const ep = endpoints();
  const token = csrfToken();

  return new Promise((resolve, reject) => {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', ep.upload, true);
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.setRequestHeader('X-CSRF-TOKEN', token);
    xhr.withCredentials = true;
    xhr.upload.onprogress = (event) => {
      if (!onProgress || !event.lengthComputable) return;
      const pct = Math.min(100, Math.max(0, Math.round((event.loaded / event.total) * 100)));
      onProgress(pct);
    };
    xhr.onload = () => {
      if (xhr.status < 200 || xhr.status >= 300) {
        let message = `Upload failed (${xhr.status})`;
        try {
          const payload = JSON.parse(xhr.responseText || '{}');
          const firstValidation = payload?.errors ? Object.values(payload.errors)?.[0]?.[0] : '';
          message = payload?.message || firstValidation || message;
        } catch (e) {}
        reject(new Error(message));
        return;
      }
      try {
        const payload = JSON.parse(xhr.responseText || '{}');
        resolve(payload?.data || null);
      } catch (error) {
        reject(error);
      }
    };
    xhr.onerror = () => reject(new Error('Upload failed'));

    const fd = new FormData();
    fd.append('file', file);
    xhr.send(fd);
  });
}

async function fileToDataUrl(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => resolve(String(reader.result || ''));
    reader.onerror = () => reject(new Error('Unable to read image for upload.'));
    reader.readAsDataURL(file);
  });
}

async function uploadImageViaJson(file) {
  const ep = endpoints();
  const fileData = await fileToDataUrl(file);
  const payload = await requestJson(ep.upload, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      file_data: fileData,
      file_name: file.name || 'image.jpg',
      mime_type: file.type || 'image/jpeg',
    }),
  });
  return payload?.data || null;
}

async function readImageFile(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => resolve(reader.result);
    reader.onerror = () => reject(new Error('Unable to read selected image.'));
    reader.readAsDataURL(file);
  });
}

async function loadImageElement(src) {
  return new Promise((resolve, reject) => {
    const img = new Image();
    img.onload = () => resolve(img);
    img.onerror = () => reject(new Error('Unable to process selected image.'));
    img.src = src;
  });
}

async function maybeCompressForUpload(file, maxBytes = 1500000) {
  if (!file || file.size <= maxBytes) return file;
  if (!(file.type || '').startsWith('image/')) return file;

  const source = await readImageFile(file);
  const image = await loadImageElement(String(source));
  const canvas = document.createElement('canvas');
  const ctx = canvas.getContext('2d');
  if (!ctx) return file;

  const base = (file.name || 'image').replace(/\.[^.]+$/, '');
  const maxDims = [2000, 1700, 1400, 1200, 1000, 900, 800];
  for (let d = 0; d < maxDims.length; d += 1) {
    const maxDim = maxDims[d];
    const scale = Math.min(1, maxDim / Math.max(image.width, image.height));
    canvas.width = Math.max(1, Math.round(image.width * scale));
    canvas.height = Math.max(1, Math.round(image.height * scale));
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.drawImage(image, 0, 0, canvas.width, canvas.height);

    let quality = 0.86;
    while (quality >= 0.26) {
      // Always convert to JPEG for strongest compression against strict server limits.
      const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', quality));
      if (blob && blob.size <= maxBytes) {
        return new File([blob], `${base}.jpg`, { type: 'image/jpeg' });
      }
      quality -= 0.1;
    }
  }

  return file;
}

async function updateMediaMeta(id, payload) {
  const ep = endpoints();
  const body = {
    id,
    title: payload?.title ?? '',
    alt_text: payload?.alt_text ?? '',
  };
  const res = await requestJson(ep.update, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(body),
  });
  return res?.data || null;
}

async function openPicker({
  multiple = false,
  selectedIds = [],
  onSelect,
  type = 'all',
  modalTitle = 'Media Manager',
  actionLabel = '',
} = {}) {
  const $ = window.jQuery;
  if (!$ || !$.fn?.modal) return;

  const $modal = $(createModalHtml({ modalTitle, actionLabel: actionLabel || 'Insert selected' })).appendTo(document.body);
  const modalEl = $modal[0];
  const grid = modalEl.querySelector('.js-editor-media-grid');
  const empty = modalEl.querySelector('.js-editor-media-empty');
  const insertBtn = modalEl.querySelector('.js-editor-media-insert');
  const search = modalEl.querySelector('.js-editor-media-search');
  const filterType = modalEl.querySelector('.js-wp-filter-type');
  const filterDate = modalEl.querySelector('.js-wp-filter-date');
  const details = modalEl.querySelector('.js-wpmm-details');
  const footerMeta = modalEl.querySelector('.js-wpmm-footer-meta');
  const uploadInput = modalEl.querySelector('.js-editor-media-upload');
  const selectFilesBtn = modalEl.querySelector('.js-wpmm-select-files');
  const dropzone = modalEl.querySelector('.js-wpmm-dropzone');
  const uploadProgress = modalEl.querySelector('.js-wpmm-upload-progress');
  const uploadProgressBar = modalEl.querySelector('.js-wpmm-upload-progress-bar');
  const uploadStatus = modalEl.querySelector('.js-wpmm-upload-status');
  const tabs = modalEl.querySelectorAll('.wpmm-tab');
  const modalContent = modalEl.querySelector('.modal-content');
  const isMultipleMode = !!multiple;
  let all = [];
  let selected = [];
  let focused = null;
  let lastClickedId = null;
  let dragDepth = 0;

  if (isMultipleMode) {
    modalEl.classList.add('is-multiple-mode');
  }

  if (uploadInput) {
    uploadInput.multiple = isMultipleMode;
  }

  if (filterType && type !== 'all') {
    filterType.value = type;
  }
  updateActionText(insertBtn, isMultipleMode, actionLabel);
  updateFooterMeta(footerMeta, 0, isMultipleMode);

  const bindDetailActions = () => {
    const copyBtn = details?.querySelector('.js-wpmm-copy-url');
    const urlInput = details?.querySelector('.js-wpmm-file-url');
    const hint = details?.querySelector('.js-wpmm-save-hint');
    if (!copyBtn || !urlInput || !hint) return;
    copyBtn?.addEventListener('click', async () => {
      const url = String(urlInput?.value || '').trim();
      if (!url) return;
      try {
        await navigator.clipboard.writeText(url);
        hint.textContent = 'URL copied';
        setTimeout(() => {
          const newHint = details?.querySelector('.js-wpmm-save-hint');
          if (newHint && newHint.textContent === 'URL copied') newHint.textContent = '';
        }, 1200);
      } catch (err) {
        hint.textContent = 'Copy failed';
      }
    });
  };

  const apply = () => {
    const q = String(search?.value || '').toLowerCase();
    const t = filterType?.value || 'all';
    const d = filterDate?.value || 'all';
    const list = all.filter((item) => {
      const matchType = t === 'all' ? true : item.type === t;
      const matchQuery = !q || String(item.name || '').toLowerCase().includes(q);
      const matchDate = d === 'all' ? true : monthKeyFromItem(item) === d;
      return matchType && matchQuery && matchDate;
    });
    grid.innerHTML = '';
    if (!list.length) {
      empty.classList.remove('d-none');
    } else {
      empty.classList.add('d-none');
      list.forEach((item) => {
        const isSelected = selected.some((s) => s.id === item.id);
        const selectedIndex = selected.findIndex((s) => s.id === item.id);
        const orderLabel = selectedIndex >= 0 ? String(selectedIndex + 1) : '';
        const checkText = isMultipleMode
          ? (orderLabel.length > 2 ? '99+' : orderLabel)
          : (isSelected ? '&#10003;' : '');
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = `shopadmin-editor-media-item wpmm-grid-item${isSelected ? ' is-selected' : ''}`;
        btn.innerHTML = `${mediaThumb(item)}<span class="wpmm-check${isMultipleMode ? ' wpmm-check--order' : ''}">${checkText}</span><div class="shopadmin-editor-media-meta">${item.name || ''}</div>`;
        btn.addEventListener('click', (event) => {
          focused = item;
          if (isMultipleMode) {
            const currentIndex = list.findIndex((it) => it.id === item.id);
            const anchorIndex = list.findIndex((it) => it.id === lastClickedId);
            if (event.shiftKey && currentIndex >= 0 && anchorIndex >= 0) {
              const from = Math.min(anchorIndex, currentIndex);
              const to = Math.max(anchorIndex, currentIndex);
              const range = list.slice(from, to + 1);
              selected = [...selected];
              range.forEach((rangeItem) => {
                if (!selected.some((s) => s.id === rangeItem.id)) {
                  selected.push(rangeItem);
                }
              });
            } else {
              const exists = selected.some((s) => s.id === item.id);
              selected = exists ? selected.filter((s) => s.id !== item.id) : [...selected, item];
            }
            lastClickedId = item.id;
          } else {
            selected = [item];
          }
          insertBtn.disabled = selected.length === 0;
          updateFooterMeta(footerMeta, selected.length, isMultipleMode);
          apply();
        });
        grid.appendChild(btn);
      });
    }
    if (!focused && selected[0]) focused = selected[0];
    renderDetailsPanel(details, focused, isMultipleMode, selected.length);
    bindDetailActions();
    updateFooterMeta(footerMeta, selected.length, isMultipleMode);
  };

  $modal.on('shown.bs.modal', async () => {
    const preselected = await resolveMediaIds(normalizeIds(selectedIds));
    selected = preselected;
    focused = selected[0] || null;
    all = await loadMedia(modalEl);
    refreshDateFilter(filterDate, all);
    insertBtn.disabled = selected.length === 0;
    updateFooterMeta(footerMeta, selected.length, isMultipleMode);
    apply();
  });

  tabs.forEach((tab) => tab.addEventListener('click', () => activatePane(modalEl, tab.getAttribute('data-tab') || 'library')));
  filterType?.addEventListener('change', apply);
  filterDate?.addEventListener('change', apply);
  search?.addEventListener('input', apply);
  selectFilesBtn?.addEventListener('click', () => uploadInput?.click());
  const setUploadUi = (visible, percent = 0, statusText = '') => {
    if (uploadProgress) {
      uploadProgress.hidden = !visible;
    }
    if (uploadProgressBar) {
      uploadProgressBar.style.width = `${percent}%`;
    }
    if (uploadStatus) {
      uploadStatus.hidden = !visible;
      uploadStatus.textContent = statusText;
    }
  };
  const handleUpload = async (files) => {
    const incoming = Array.from(files || []).filter(Boolean);
    if (!incoming.length) return;
    const queue = isMultipleMode ? incoming : [incoming[0]];
    let failed = false;
    const uploadedItems = [];
    try {
      for (let i = 0; i < queue.length; i += 1) {
        const file = queue[i];
        const prefix = queue.length > 1 ? `${i + 1}/${queue.length} ` : '';
        setUploadUi(true, 0, `${prefix}Uploading 0%`);
        const safeFile = await maybeCompressForUpload(file);
        if (safeFile.size > 1500000 && (safeFile.type || '').startsWith('image/')) {
          throw new Error('Upload failed: image is too large for current server limit. Please use a smaller image.');
        }
        const isImage = (safeFile.type || '').startsWith('image/');
        const uploaded = isImage
          ? await uploadImageViaJson(safeFile)
          : await uploadMediaFile(safeFile, {
            onProgress: (pct) => setUploadUi(true, pct, `${prefix}Uploading ${pct}%`),
          });
        if (uploaded) {
          uploadedItems.push(uploaded);
        }
      }

      if (!uploadedItems.length) {
        failed = true;
        setUploadUi(true, 0, 'Upload failed');
        return;
      }

      setUploadUi(true, 100, 'Upload complete');
      all = await loadMedia(modalEl);
      refreshDateFilter(filterDate, all);
      focused = uploadedItems[uploadedItems.length - 1];
      selected = isMultipleMode
        ? [...selected, ...uploadedItems.filter((item) => !selected.some((s) => s.id === item.id))]
        : [focused];
      insertBtn.disabled = selected.length === 0;
      updateFooterMeta(footerMeta, selected.length, isMultipleMode);
      activatePane(modalEl, 'library');
      apply();
    } catch (error) {
      failed = true;
      const message = error?.message ? String(error.message) : 'Upload failed';
      setUploadUi(true, 0, message);
      if (window.Toast?.error) {
        window.Toast.error(message, 0);
      }
      if (window.toast?.error) {
        window.toast.error(message, 60000);
      }
    } finally {
      if (!failed) {
        setTimeout(() => setUploadUi(false, 0, ''), 1200);
      }
    }
  };

  const hasFilesInDrag = (event) => {
    const types = event?.dataTransfer?.types;
    if (!types) return false;
    return Array.from(types).includes('Files');
  };

  const setGlobalDragState = (active) => {
    dropzone?.classList.toggle('is-dragover', active);
    modalContent?.classList.toggle('is-dragover', active);
  };

  const handleGlobalDragEnter = (e) => {
    if (!hasFilesInDrag(e)) return;
    e.preventDefault();
    e.stopPropagation();
    dragDepth += 1;
    setGlobalDragState(true);
  };

  const handleGlobalDragOver = (e) => {
    if (!hasFilesInDrag(e)) return;
    e.preventDefault();
    e.stopPropagation();
    if (e.dataTransfer) e.dataTransfer.dropEffect = 'copy';
    setGlobalDragState(true);
  };

  const handleGlobalDragLeave = (e) => {
    if (!hasFilesInDrag(e)) return;
    e.preventDefault();
    e.stopPropagation();
    dragDepth = Math.max(0, dragDepth - 1);
    if (dragDepth === 0) {
      setGlobalDragState(false);
    }
  };

  const handleGlobalDrop = async (e) => {
    if (!hasFilesInDrag(e)) return;
    e.preventDefault();
    e.stopPropagation();
    dragDepth = 0;
    setGlobalDragState(false);
    activatePane(modalEl, 'upload');
    await handleUpload(e.dataTransfer?.files || []);
  };

  ['dragenter', 'dragover', 'dragleave', 'drop'].forEach((eventName) => {
    modalEl.addEventListener(eventName, (e) => {
      if (!hasFilesInDrag(e)) return;
      e.preventDefault();
      e.stopPropagation();
    });
  });

  modalEl.addEventListener('dragenter', handleGlobalDragEnter);
  modalEl.addEventListener('dragover', handleGlobalDragOver);
  modalEl.addEventListener('dragleave', handleGlobalDragLeave);
  modalEl.addEventListener('drop', handleGlobalDrop);
  uploadInput?.addEventListener('change', async function onUploadChange() {
    await handleUpload(this.files || []);
    this.value = '';
  });
  insertBtn.addEventListener('click', () => {
    const payload = selected.length ? selected : ((!isMultipleMode && focused) ? [focused] : []);
    onSelect?.(payload);
    $modal.modal('hide');
  });

  $modal.on('hidden.bs.modal', () => $modal.remove());
  $modal.modal('show');
}

function bindTriggers() {
  document.querySelectorAll(SELECTOR_TRIGGER).forEach((trigger) => {
    if (trigger.dataset.mediaPickerBound === '1') return;
    trigger.dataset.mediaPickerBound = '1';
    trigger.addEventListener('click', async () => {
      const inputSel = trigger.getAttribute('data-input');
      const previewSel = trigger.getAttribute('data-preview');
      const multipleAttr = String(trigger.getAttribute('data-multiple') || '').toLowerCase().trim();
      const multiple = multipleAttr === 'true' || multipleAttr === '1' || multipleAttr === 'yes';
      const type = trigger.getAttribute('data-type') || 'all';
      const input = inputSel ? document.querySelector(inputSel) : null;
      const preview = previewSel ? document.querySelector(previewSel) : null;
      const ids = input ? normalizeIds(input.value) : [];
      const inputName = (input?.getAttribute('name') || '').toLowerCase();
      const inputId = (input?.id || '').toLowerCase();
      const key = `${inputName} ${inputId}`;
      const isGallery = key.includes('gallery');
      const isProductImage = key.includes('thumbnail') || key.includes('product_image') || key.includes('image_media_id');
      const modalTitle = trigger.getAttribute('data-modal-title')
        || (isGallery ? 'Product gallery' : (isProductImage ? 'Product image' : 'Media Manager'));
      const actionLabel = trigger.getAttribute('data-action-label')
        || (isGallery ? 'Add to gallery' : (isProductImage ? 'Set product image' : 'Insert selected'));
      await openPicker({
        multiple,
        type,
        modalTitle,
        actionLabel,
        selectedIds: ids,
        onSelect: async (items) => {
          if (!input) return;
          const nextIds = (items || []).map((it) => it.id).filter(Boolean);
          input.value = multiple ? nextIds.join(',') : (nextIds[0] || '');
          input.dispatchEvent(new Event('input', { bubbles: true }));
          input.dispatchEvent(new Event('change', { bubbles: true }));
          renderFieldPreview(preview, items || [], multiple, (idx) => {
            const copy = [...(items || [])];
            copy.splice(idx, 1);
            const idsAfter = copy.map((it) => it.id).filter(Boolean);
            input.value = multiple ? idsAfter.join(',') : (idsAfter[0] || '');
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
            renderFieldPreview(preview, copy, multiple, () => {});
          });
        },
      });
    });
  });
}

window.ShopMediaPicker = {
  open: openPicker,
  bind: bindTriggers,
  resolve: resolveMediaIds,
};

document.addEventListener('DOMContentLoaded', bindTriggers);

