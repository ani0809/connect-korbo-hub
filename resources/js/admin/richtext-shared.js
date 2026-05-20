const AIZ_DEFAULT_TOOLBAR = [
  ['font', ['bold', 'underline', 'italic', 'clear']],
  ['para', ['ul', 'ol', 'paragraph']],
  ['style', ['style']],
  ['color', ['color']],
  ['table', ['table']],
  ['insert', ['link', 'picture', 'video', 'mediaManager']],
  ['view', ['fullscreen', 'undo', 'redo']],
];

const EDITOR_MEDIA_LIST_URL = '/admin/editor-media/list';
const EDITOR_MEDIA_UPLOAD_URL = '/admin/editor-media/upload';

function editorCsrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function ensureMediaManagerStyles() {
  if (document.getElementById('shopadmin-editor-media-manager-styles')) return;
  const style = document.createElement('style');
  style.id = 'shopadmin-editor-media-manager-styles';
  style.textContent = `
    .shopadmin-editor-media-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(110px,1fr)); gap:10px; max-height:340px; overflow:auto; margin-top:10px; }
    .shopadmin-editor-media-item { border:1px solid #d8dee4; border-radius:6px; background:#fff; padding:6px; cursor:pointer; }
    .shopadmin-editor-media-item.is-selected { border-color:#3b82f6; box-shadow:0 0 0 2px rgba(59,130,246,.2); }
    .shopadmin-editor-media-thumb { width:100%; aspect-ratio:1/1; object-fit:cover; background:#f3f4f6; border-radius:4px; display:block; }
    .shopadmin-editor-media-meta { margin-top:6px; font-size:11px; color:#64748b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .shopadmin-editor-media-empty { color:#64748b; font-size:13px; padding:12px 2px; }
  `;
  document.head.appendChild(style);
}

function withMediaManagerButton(toolbar) {
  const out = Array.isArray(toolbar) ? toolbar.map((grp) => [grp[0], [...(grp[1] || [])]]) : [];
  const insertGroup = out.find((grp) => grp[0] === 'insert');
  if (insertGroup) {
    if (!insertGroup[1].includes('mediaManager')) insertGroup[1].push('mediaManager');
  } else {
    out.push(['insert', ['mediaManager']]);
  }
  return out;
}

async function uploadEditorMediaFile(file) {
  const formData = new FormData();
  formData.append('file', file);
  const res = await fetch(EDITOR_MEDIA_UPLOAD_URL, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': editorCsrfToken(),
      Accept: 'application/json',
    },
    body: formData,
    credentials: 'same-origin',
  });
  if (!res.ok) throw new Error(`Upload failed (${res.status})`);
  const payload = await res.json();
  if (!payload?.success || !payload?.data?.url) throw new Error('Invalid upload response');
  return payload.data;
}

function insertMediaIntoEditor($editor, mediaItem) {
  if (!mediaItem?.url) return;
  const escapedUrl = String(mediaItem.url).replace(/"/g, '&quot;');
  if (mediaItem.type === 'video') {
    $editor.summernote('pasteHTML', `<p><video controls preload="metadata" style="max-width:100%;height:auto;" src="${escapedUrl}"></video></p>`);
    return;
  }
  $editor.summernote('insertImage', mediaItem.url, mediaItem.name || 'image');
}

async function handleDroppedOrPastedFiles($editor, files) {
  const mediaFiles = Array.from(files || []).filter((file) => {
    const t = file?.type || '';
    return t.startsWith('image/') || t.startsWith('video/');
  });
  for (const file of mediaFiles) {
    try {
      const item = await uploadEditorMediaFile(file);
      insertMediaIntoEditor($editor, item);
    } catch (err) {
      window.Toast?.error?.(err?.message || 'Media upload failed.');
    }
  }
}

async function loadEditorMediaLibrary($grid) {
  const res = await fetch(EDITOR_MEDIA_LIST_URL, {
    headers: { Accept: 'application/json' },
    credentials: 'same-origin',
  });
  if (!res.ok) throw new Error(`List failed (${res.status})`);
  const payload = await res.json();
  return Array.isArray(payload?.data) ? payload.data : [];
}

function buildMediaManagerModal($, onSelect) {
  ensureMediaManagerStyles();
  const id = `shopadmin-editor-media-modal-${Date.now()}`;
  const html = `
    <div class="modal fade shopadmin-editor-media-modal" id="${id}" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Media Manager</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          </div>
          <div class="modal-body">
            <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:8px;">
              <input type="file" class="form-control-file js-editor-media-upload" accept="image/*,video/*" />
              <small class="text-muted ml-2">Image/video upload + library insert</small>
            </div>
            <div class="d-flex align-items-center justify-content-between flex-wrap mt-2" style="gap:8px;">
              <div class="btn-group btn-group-sm js-editor-media-tabs" role="group">
                <button type="button" class="btn btn-light active" data-filter="all">All</button>
                <button type="button" class="btn btn-light" data-filter="image">Images</button>
                <button type="button" class="btn btn-light" data-filter="video">Videos</button>
              </div>
              <input type="text" class="form-control form-control-sm js-editor-media-search" placeholder="Search media..." style="max-width:240px;" />
            </div>
            <div class="shopadmin-editor-media-grid js-editor-media-grid"></div>
            <div class="shopadmin-editor-media-empty js-editor-media-empty d-none">No media found.</div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary js-editor-media-insert" disabled>Insert Selected</button>
          </div>
        </div>
      </div>
    </div>`;
  const $modal = $(html).appendTo(document.body);
  const $grid = $modal.find('.js-editor-media-grid');
  const $empty = $modal.find('.js-editor-media-empty');
  const $insert = $modal.find('.js-editor-media-insert');
  const $tabs = $modal.find('.js-editor-media-tabs [data-filter]');
  const $search = $modal.find('.js-editor-media-search');
  let selected = null;
  let allItems = [];
  let activeFilter = 'all';
  let query = '';

  const render = (items) => {
    $grid.empty();
    if (!items.length) {
      $empty.removeClass('d-none');
      return;
    }
    $empty.addClass('d-none');
    items.forEach((item) => {
      const thumb = item.type === 'video'
        ? `<video class="shopadmin-editor-media-thumb" src="${item.url}" muted></video>`
        : `<img class="shopadmin-editor-media-thumb" src="${item.url}" alt="${item.name || 'media'}" />`;
      const $item = $(`<button type="button" class="shopadmin-editor-media-item">${thumb}<div class="shopadmin-editor-media-meta">${item.name || ''}</div></button>`);
      $item.on('click', () => {
        selected = item;
        $grid.find('.shopadmin-editor-media-item').removeClass('is-selected');
        $item.addClass('is-selected');
        $insert.prop('disabled', false);
      });
      $grid.append($item);
    });
  };

  const applyFilters = () => {
    const q = query.trim().toLowerCase();
    const filtered = allItems.filter((item) => {
      const typeOk = activeFilter === 'all' ? true : item.type === activeFilter;
      const name = String(item.name || '').toLowerCase();
      const queryOk = q ? name.includes(q) : true;
      return typeOk && queryOk;
    });
    render(filtered);
  };

  $modal.on('shown.bs.modal', async () => {
    try {
      allItems = await loadEditorMediaLibrary($grid);
      applyFilters();
    } catch {
      render([]);
    }
  });

  $modal.find('.js-editor-media-upload').on('change', async function onUpload() {
    const file = this.files?.[0];
    if (!file) return;
    try {
      const uploaded = await uploadEditorMediaFile(file);
      selected = uploaded;
      allItems = await loadEditorMediaLibrary($grid);
      applyFilters();
    } catch (err) {
      window.Toast?.error?.(err?.message || 'Upload failed.');
    } finally {
      this.value = '';
    }
  });

  $tabs.on('click', function onTabClick() {
    activeFilter = this.getAttribute('data-filter') || 'all';
    $tabs.removeClass('active');
    $(this).addClass('active');
    applyFilters();
  });

  $search.on('input', function onSearchInput() {
    query = this.value || '';
    applyFilters();
  });

  $insert.on('click', () => {
    if (selected) onSelect(selected);
    $modal.modal('hide');
  });

  $modal.on('hidden.bs.modal', () => {
    $modal.remove();
  });

  $modal.modal('show');
}

window.initBrandedRichText = (options = {}) => {
  const $ = window.jQuery;
  if (!$ || !$.fn || typeof $.fn.summernote !== 'function') return;

  if (!window.__shopadminSummernoteHelpCommandPatched) {
    window.__shopadminSummernoteHelpCommandPatched = true;

    const nativeSummernoteFn = $.fn.summernote;
    $.fn.summernote = function patchedSummernote(...args) {
      const cmd = args[0];
      if (typeof cmd === 'string' && cmd.toLowerCase().includes('help')) {
        return this;
      }
      return nativeSummernoteFn.apply(this, args);
    };

    if ($.summernote?.plugins) {
      $.summernote.plugins.helpDialog = function disabledHelpDialog() {
        this.initialize = function noop() {};
        this.destroy = function noop() {};
        this.show = function noopShow() { return false; };
      };
    }
  }

  if (!window.__shopadminSummernoteModalPatchBound) {
    window.__shopadminSummernoteModalPatchBound = true;
    $(document).on('shown.bs.modal', '.note-modal', function normalizeNoteModal() {
      const modal = this;
      modal.style.position = 'fixed';
      modal.style.inset = '0';
      modal.style.zIndex = '1050';
      modal.style.display = 'block';
      modal.style.width = '100%';
      modal.style.height = '100%';
      modal.style.maxWidth = 'none';
      modal.style.maxHeight = 'none';
      modal.style.background = 'transparent';
      modal.style.border = '0';
      modal.style.borderRadius = '0';
      modal.style.boxShadow = 'none';
      modal.style.padding = '0';
      modal.style.overflowX = 'hidden';
      modal.style.overflowY = 'auto';

      const dialog = modal.querySelector('.modal-dialog');
      if (dialog) {
        dialog.style.maxWidth = '500px';
        dialog.style.margin = '1.75rem auto';
        dialog.style.pointerEvents = 'none';
      }

      const content = modal.querySelector('.modal-content');
      if (content) {
        content.style.pointerEvents = 'auto';
      }
    });

    // Fully disable Summernote Help dialog/actions.
    $(document).on('click', '[data-event="showHelpDialog"]', function blockHelpClick(e) {
      e.preventDefault();
      e.stopImmediatePropagation();
      return false;
    });

    $(document).on('shown.bs.modal', '.note-modal, .note-help-dialog', function forceCloseHelpModal() {
      const $modal = $(this);
      const hasHelpContent = $modal.is('.note-help-dialog') || $modal.find('.note-help-dialog, .note-help').length > 0;
      if (!hasHelpContent) return;
      $modal.modal('hide');
      window.setTimeout(() => $modal.remove(), 0);
    });

    $(document).on('keydown', function blockHelpHotkeys(e) {
      const key = (e.key || '').toLowerCase();
      const isF1 = key === 'f1';
      const isShiftQuestion = e.shiftKey && (key === '?' || key === '/' || e.code === 'Slash');
      if (!isF1 && !isShiftQuestion) return;
      e.preventDefault();
      e.stopImmediatePropagation();
      return false;
    });
  }

  const {
    selector = '.shopadmin-text-editor, .aiz-text-editor',
    minHeight = 200,
    placeholder = '',
    toolbar = AIZ_DEFAULT_TOOLBAR,
    onChange,
    format = false,
  } = options;

  $(selector).each(function initEditor() {
    const $el = $(this);
    const customButtons = {
      mediaManager: () => {
        const ui = $.summernote.ui;
        return ui.button({
          contents: '<i class="note-icon-picture"></i>',
          tooltip: 'Media manager',
          click: () => {
            if (window.ShopMediaPicker?.open) {
              window.ShopMediaPicker.open({
                multiple: false,
                type: 'all',
                modalTitle: 'Insert media',
                actionLabel: 'Insert into content',
                onSelect: (items) => insertMediaIntoEditor($el, (items || [])[0]),
              });
              return;
            }
            buildMediaManagerModal($, (item) => insertMediaIntoEditor($el, item));
          },
        }).render();
      },
    };

    if ($el.next('.note-editor').length) {
      $el.summernote('destroy');
    }

    $el.summernote({
      toolbar: withMediaManagerButton($el.data('buttons') || toolbar),
      buttons: customButtons,
      placeholder: $el.attr('placeholder') || placeholder,
      disableDragAndDrop: true,
      shortcuts: false,
      height: Number($el.data('min-height') || minHeight),
      callbacks: {
        onImageUpload(files) {
          handleDroppedOrPastedFiles($el, files);
        },
        onPaste(e) {
          const clipboard = (e.originalEvent || e).clipboardData;
          const mediaFiles = Array.from(clipboard?.files || []).filter((file) => {
            const t = file?.type || '';
            return t.startsWith('image/') || t.startsWith('video/');
          });
          if (mediaFiles.length > 0) {
            e.preventDefault();
            handleDroppedOrPastedFiles($el, mediaFiles);
            return;
          }

          const shouldFormatPlain = typeof $el.data('format') !== 'undefined' ? !!$el.data('format') : !!format;
          if (!shouldFormatPlain) return;
          const bufferText = (clipboard || window.clipboardData).getData('Text');
          e.preventDefault();
          document.execCommand('insertText', false, bufferText);
        },
        onChange(contents) {
          if (typeof onChange === 'function') onChange(contents, this);
        },
      },
    });

    const videoDialog = $el.summernote('module', 'videoDialog');
    if (videoDialog && typeof videoDialog.createVideoNode === 'function') {
      const nativeCreate = videoDialog.createVideoNode;
      videoDialog.createVideoNode = function patchedVideoNode(url) {
        const wrap = $('<div class="embed-responsive embed-responsive-16by9"></div>');
        const html = $(nativeCreate.call(this, url)).addClass('embed-responsive-item');
        return wrap.append(html)[0];
      };
    }

    // Prevent clipped tooltips inside constrained admin cards.
    const $note = $el.next('.note-editor');
    if ($note.length && $.fn.tooltip) {
      $note.find('[data-toggle="tooltip"]').tooltip({ container: 'body' });
    }

    // Remove help button if any toolbar preset adds it.
    $note.find('[data-event="showHelpDialog"], .note-help-btn, button[title*="Help"], button[aria-label*="Help"]').remove();

    // Block common help hotkeys while focused in editor.
    $note.find('.note-editable').on('keydown', function preventHelpHotkeys(e) {
      if (e.key === 'F1' || (e.shiftKey && e.key === '?')) {
        e.preventDefault();
        e.stopPropagation();
      }
    });

    $note.find('.note-editable').on('drop', function handleDrop(e) {
      const dt = (e.originalEvent || e).dataTransfer;
      const files = Array.from(dt?.files || []);
      const hasMedia = files.some((file) => {
        const t = file?.type || '';
        return t.startsWith('image/') || t.startsWith('video/');
      });
      if (!hasMedia) return;
      e.preventDefault();
      handleDroppedOrPastedFiles($el, files);
    });
  });
};

