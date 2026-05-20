const nameInput = document.getElementById('name');
const slugInput = document.getElementById('slug');
const typeSelect = document.getElementById('product-type');
const variableSection = document.getElementById('variable-section');
const variationsSimpleHint = document.getElementById('variationsSimpleHint');
const dataTabs = document.getElementById('productDataTabs');
const screenOptionsBtn = document.getElementById('zpfScreenOptionsBtn');
const screenOptionsPanel = document.getElementById('zpfScreenOptions');
const sidebarMetaboxes = document.getElementById('zpfSidebarMetaboxes');
const mainMetaboxes = document.getElementById('zpfMainMetaboxes');
const productForm = document.getElementById('product-form');
const variationRows = document.getElementById('variationRows');
const variantsPayload = document.getElementById('variants-payload');
const generateVariationsBtn = document.getElementById('generateVariationsBtn');
const existingVariantsData = document.getElementById('existing-variants-data');
const submitActionInput = document.getElementById('submit_action');
const permalinkRow = document.getElementById('productPermalinkRow');
const permalinkLink = document.getElementById('productPermalinkLink');
const permalinkEditBtn = document.getElementById('productPermalinkEdit');
const permalinkEditor = document.getElementById('productPermalinkEditor');
const permalinkInput = document.getElementById('productPermalinkInput');
const permalinkSaveBtn = document.getElementById('productPermalinkSave');
const permalinkCancelBtn = document.getElementById('productPermalinkCancel');
const manageStockInput = document.getElementById('manage_stock');
const soldIndividuallyInput = document.getElementById('sold_individually');
const productLinkOptionsData = document.getElementById('product-link-options-data');

function getCategoryValue() {
  const checkedRadio = document.querySelector('input[name="category_id"]:checked');
  if (checkedRadio) return checkedRadio.value;
  const select = document.querySelector('select[name="category_id"]');
  return select?.value || '';
}

function getCategoryLabel() {
  const checkedRadio = document.querySelector('input[name="category_id"]:checked');
  if (checkedRadio) return checkedRadio.closest('label')?.textContent?.trim() || '';
  const selectedOption = document.querySelector('select[name="category_id"] option:checked');
  return selectedOption?.textContent || '';
}

function slugify(text) {
  return String(text || '').toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
}

function updatePermalinkUI() {
  if (!permalinkRow || !slugInput) return;
  const baseUrl = permalinkRow.getAttribute('data-base-url') || '';
  const slug = String(slugInput.value || '').trim();
  const nameValue = String(nameInput?.value || '').trim();
  const shouldShow = Boolean(slug || nameValue);

  permalinkRow.hidden = !shouldShow;
  if (!shouldShow) return;

  const href = slug ? `${baseUrl}/${slug}` : `${baseUrl}/`;
  if (permalinkLink) {
    permalinkLink.textContent = href;
    permalinkLink.setAttribute('href', slug ? href : '#');
  }
  if (permalinkInput && document.activeElement !== permalinkInput) {
    permalinkInput.value = slug;
  }
}

function setPermalinkEditing(editing) {
  if (!permalinkEditor || !permalinkEditBtn) return;
  permalinkEditor.hidden = !editing;
  permalinkEditBtn.hidden = editing;
  if (editing) {
    permalinkInput?.focus();
    permalinkInput?.select();
  }
}

function parseJsonScript(el, fallback = []) {
  if (!el) return fallback;
  try {
    return JSON.parse(el.textContent || '[]');
  } catch {
    return fallback;
  }
}

let currentVariants = Array.isArray(parseJsonScript(existingVariantsData, [])) ? parseJsonScript(existingVariantsData, []) : [];
const productLinkOptions = Array.isArray(parseJsonScript(productLinkOptionsData, [])) ? parseJsonScript(productLinkOptionsData, []) : [];

nameInput?.addEventListener('input', () => {
  if (!slugInput?.dataset.edited) slugInput.value = slugify(nameInput.value);
  updatePermalinkUI();
});
slugInput?.addEventListener('input', () => {
  slugInput.dataset.edited = '1';
  updatePermalinkUI();
});

permalinkEditBtn?.addEventListener('click', () => {
  if (permalinkInput && slugInput) {
    permalinkInput.value = slugInput.value || '';
  }
  setPermalinkEditing(true);
});

permalinkSaveBtn?.addEventListener('click', () => {
  if (!slugInput || !permalinkInput) return;
  slugInput.value = slugify(permalinkInput.value);
  slugInput.dataset.edited = '1';
  updatePermalinkUI();
  setPermalinkEditing(false);
});

permalinkCancelBtn?.addEventListener('click', () => {
  if (permalinkInput && slugInput) {
    permalinkInput.value = slugInput.value || '';
  }
  setPermalinkEditing(false);
});

permalinkInput?.addEventListener('keydown', (event) => {
  if (event.key === 'Enter') {
    event.preventDefault();
    permalinkSaveBtn?.click();
  }
  if (event.key === 'Escape') {
    event.preventDefault();
    permalinkCancelBtn?.click();
  }
});

function updateTypeSections() {
  const t = typeSelect?.value;
  variableSection?.classList.toggle('hidden', t !== 'variable');
  variationsSimpleHint?.classList.toggle('hidden', t === 'variable');
}
typeSelect?.addEventListener('change', updateTypeSections);
updateTypeSections();

function syncInventoryUI() {
  const manageStock = Boolean(manageStockInput?.checked);
  const managedGroups = document.querySelectorAll('[data-inventory-role="managed-stock-fields"]');
  const stockStatusGroup = document.querySelector('[data-inventory-role="stock-status"]');
  const minQtyInput = productForm?.querySelector('input[name="min_purchase_qty"]');
  const maxQtyInput = productForm?.querySelector('input[name="max_purchase_qty"]');

  managedGroups.forEach((group) => {
    group.classList.toggle('hidden', !manageStock);
    group.querySelectorAll('input, select, textarea').forEach((field) => {
      field.disabled = !manageStock;
    });
  });

  if (stockStatusGroup) {
    stockStatusGroup.classList.toggle('hidden', manageStock);
  }

  stockStatusGroup?.querySelectorAll('input[type="radio"]').forEach((field) => {
    field.disabled = manageStock;
  });

  if (soldIndividuallyInput?.checked) {
    if (minQtyInput) {
      minQtyInput.value = '1';
      minQtyInput.readOnly = true;
    }
    if (maxQtyInput) {
      maxQtyInput.value = '1';
      maxQtyInput.readOnly = true;
    }
  } else {
    if (minQtyInput) minQtyInput.readOnly = false;
    if (maxQtyInput) maxQtyInput.readOnly = false;
  }
}

manageStockInput?.addEventListener('change', syncInventoryUI);
soldIndividuallyInput?.addEventListener('change', syncInventoryUI);
syncInventoryUI();

function formatRelatedProduct(product) {
  if (!product) return '';
  return `${product.name}${product.sku ? ` (#${product.sku})` : ''}`;
}

function findProductOption(value) {
  const normalized = String(value || '').trim().toLowerCase();
  if (!normalized) return null;
  return productLinkOptions.find((option) => (
    String(option.id) === normalized
    || formatRelatedProduct(option).toLowerCase() === normalized
    || String(option.name || '').trim().toLowerCase() === normalized
  )) || null;
}

function syncRelatedField(fieldEl) {
  const hiddenInput = fieldEl?.querySelector('input[type="hidden"][name]');
  const chips = Array.from(fieldEl?.querySelectorAll('[data-related-id]') || [])
    .map((chip) => Number(chip.getAttribute('data-related-id')))
    .filter(Boolean);
  if (hiddenInput) {
    hiddenInput.value = [...new Set(chips)].join(',');
  }

  const bundleEmpty = document.querySelector('[data-bundle-empty]');
  if (bundleEmpty && hiddenInput?.name === 'bundle_products') {
    bundleEmpty.hidden = chips.length > 0;
  }
}

function addRelatedChip(fieldEl, product) {
  if (!fieldEl || !product?.id) return;
  const selectedWrap = fieldEl.querySelector('[data-related-selected]');
  if (!selectedWrap || selectedWrap.querySelector(`[data-related-id="${product.id}"]`)) return;

  const chip = document.createElement('button');
  chip.type = 'button';
  chip.className = 'zpf-related-chip';
  chip.setAttribute('data-related-id', String(product.id));
  chip.innerHTML = `${escapeHtml(product.name)}${product.sku ? ` <span>#${escapeHtml(product.sku)}</span>` : ''}`;
  selectedWrap.appendChild(chip);
  syncRelatedField(fieldEl);
}

function initRelatedProductFields() {
  document.querySelectorAll('[data-related-field]').forEach((fieldEl) => {
    const searchInput = fieldEl.querySelector('[data-related-search]');
    syncRelatedField(fieldEl);

    searchInput?.addEventListener('keydown', (event) => {
      if (event.key !== 'Enter') return;
      event.preventDefault();
      const product = findProductOption(searchInput.value);
      if (product) {
        addRelatedChip(fieldEl, product);
      }
      searchInput.value = '';
    });

    searchInput?.addEventListener('change', () => {
      const product = findProductOption(searchInput.value);
      if (product) {
        addRelatedChip(fieldEl, product);
      }
      searchInput.value = '';
    });

    fieldEl.addEventListener('click', (event) => {
      const chip = event.target.closest('[data-related-id]');
      if (!chip) return;
      chip.remove();
      syncRelatedField(fieldEl);
    });
  });
}

function getAttributeValues(group) {
  return Array.from(group?.querySelectorAll('input[name^="attribute_values"][type="checkbox"]:checked') || []);
}

function setAttributeGroupActive(group, active) {
  if (!group) return;
  group.classList.toggle('is-collapsed-empty', !active);
}

function refreshAttributeGroupState(group) {
  const hasValues = getAttributeValues(group).length > 0;
  const hasVariation = !!group?.querySelector('input[name$="[variation]"]')?.checked;
  setAttributeGroupActive(group, hasValues || hasVariation);
}

function initAttributeManager() {
  const picker = document.getElementById('attributePicker');
  const addAllBtn = document.getElementById('addAllAttributesBtn');
  const saveBtn = document.getElementById('saveAttributesBtn');
  const bundlesManagerBtn = document.getElementById('openBundlesManagerBtn');

  document.querySelectorAll('[data-attribute-group]').forEach((group) => {
    refreshAttributeGroupState(group);
    group.addEventListener('change', (event) => {
      if (event.target.matches('input[type="checkbox"]')) {
        refreshAttributeGroupState(group);
      }
    });
  });

  addAllBtn?.addEventListener('click', () => {
    document.querySelectorAll('[data-attribute-group]').forEach((group) => setAttributeGroupActive(group, true));
  });

  picker?.addEventListener('change', () => {
    const attributeId = picker.value;
    if (!attributeId) return;
    const group = document.querySelector(`[data-attribute-group][data-attribute-id="${attributeId}"]`);
    setAttributeGroupActive(group, true);
    group?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    picker.value = '';
  });

  document.querySelectorAll('[data-attribute-remove]').forEach((button) => {
    button.addEventListener('click', () => {
      const group = button.closest('[data-attribute-group]');
      group?.querySelectorAll('input[type="checkbox"]').forEach((input) => {
        input.checked = false;
      });
      refreshAttributeGroupState(group);
      if (typeSelect?.value === 'variable') {
        hydrateGeneratedVariations();
      }
    });
  });

  saveBtn?.addEventListener('click', () => {
    if (typeSelect?.value === 'variable') {
      hydrateGeneratedVariations();
    }
  });

  bundlesManagerBtn?.addEventListener('click', () => {
    document.querySelector('[data-target-input="bundle_products"] [data-related-search]')?.focus();
  });
}

dataTabs?.addEventListener('click', (e) => {
  const btn = e.target.closest('button[data-tab]');
  if (!btn) return;
  const tab = btn.getAttribute('data-tab');
  dataTabs.querySelectorAll('button[data-tab]').forEach((b) => b.classList.toggle('is-active', b === btn));
  document.querySelectorAll('.zpf-data-panel').forEach((panel) => {
    panel.classList.toggle('is-active', panel.getAttribute('data-panel') === tab);
  });
});

if (screenOptionsBtn && screenOptionsPanel) {
  screenOptionsBtn.addEventListener('click', () => {
    const isOpen = !screenOptionsPanel.hidden;
    screenOptionsPanel.hidden = isOpen;
    screenOptionsBtn.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
  });
  document.addEventListener('click', (e) => {
    if (!screenOptionsPanel.contains(e.target) && !screenOptionsBtn.contains(e.target)) {
      screenOptionsPanel.hidden = true;
      screenOptionsBtn.setAttribute('aria-expanded', 'false');
    }
  });
  screenOptionsPanel.querySelectorAll('input[data-box-toggle]').forEach((checkbox) => {
    const box = document.querySelector(`[data-box="${checkbox.dataset.boxToggle}"]`);
    if (!box) return;
    checkbox.checked = !box.classList.contains('hidden');
    checkbox.addEventListener('change', () => {
      box.classList.toggle('hidden', !checkbox.checked);
    });
  });
}

document.querySelectorAll('.zpf-card [data-collapse]').forEach((btn) => {
  btn.addEventListener('click', () => {
    const card = btn.closest('.zpf-card');
    card?.classList.toggle('is-collapsed');
  });
});

function initMetaboxDrag(container, attrName, storageKey) {
  if (!container) return;
  let draggingEl = null;
  const selector = `.zpf-card[${attrName}]`;

  const applySavedOrder = () => {
    try {
      const saved = JSON.parse(localStorage.getItem(storageKey) || '[]');
      if (!Array.isArray(saved) || !saved.length) return;
      saved.forEach((id) => {
        const el = container.querySelector(`[${attrName}="${id}"]`);
        if (el) container.appendChild(el);
      });
    } catch (e) {}
  };

  const saveCurrentOrder = () => {
    const ids = Array.from(container.querySelectorAll(`[${attrName}]`))
      .map((el) => el.getAttribute(attrName))
      .filter(Boolean);
    localStorage.setItem(storageKey, JSON.stringify(ids));
  };

  const bindCard = (card) => {
    const handle = card.querySelector('[data-drag-handle]');

    handle?.addEventListener('dragstart', (e) => {
      draggingEl = card;
      card.classList.add('is-dragging');
      if (e.dataTransfer) {
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', card.getAttribute(attrName) || '');
      }
    });

    handle?.addEventListener('dragend', () => {
      card.classList.remove('is-dragging');
      container.querySelectorAll('.zpf-drop-target').forEach((el) => el.classList.remove('zpf-drop-target'));
      draggingEl = null;
      saveCurrentOrder();
    });

    card.addEventListener('dragstart', (e) => {
      const handle = e.target.closest('[data-drag-handle]');
      if (!handle) {
        e.preventDefault();
        return;
      }
      draggingEl = card;
      card.classList.add('is-dragging');
      e.dataTransfer.effectAllowed = 'move';
    });

    card.addEventListener('dragend', () => {
      card.classList.remove('is-dragging');
      container.querySelectorAll('.zpf-drop-target').forEach((el) => el.classList.remove('zpf-drop-target'));
      draggingEl = null;
      saveCurrentOrder();
    });

    card.addEventListener('dragover', (e) => {
      if (!draggingEl || draggingEl === card) return;
      e.preventDefault();
      card.classList.add('zpf-drop-target');
    });

    card.addEventListener('dragleave', () => {
      card.classList.remove('zpf-drop-target');
    });

    card.addEventListener('drop', (e) => {
      if (!draggingEl || draggingEl === card) return;
      e.preventDefault();
      card.classList.remove('zpf-drop-target');
      const rect = card.getBoundingClientRect();
      const placeAfter = (e.clientY - rect.top) > rect.height / 2;
      if (placeAfter) {
        card.insertAdjacentElement('afterend', draggingEl);
      } else {
        card.insertAdjacentElement('beforebegin', draggingEl);
      }
      saveCurrentOrder();
    });
  };

  container.querySelectorAll(selector).forEach(bindCard);

  applySavedOrder();
}

initMetaboxDrag(sidebarMetaboxes, 'data-metabox-id', 'zpf_sidebar_metabox_order_v1');
initMetaboxDrag(mainMetaboxes, 'data-main-metabox-id', 'zpf_main_metabox_order_v1');

function getSelectedAttributeGroups() {
  return Array.from(document.querySelectorAll('[data-attribute-group]'))
    .map((group) => {
      const attributeId = Number(group.getAttribute('data-attribute-id'));
      const label = group.querySelector('.zpf-attribute-header strong')?.textContent?.trim() || `Attribute ${attributeId}`;
      const isVariation = !!group.querySelector('input[name$="[variation]"]')?.checked;
      const values = Array.from(group.querySelectorAll('input[name^="attribute_values"][type="checkbox"]:checked'))
        .map((input) => ({
          id: Number(input.value),
          label: input.parentElement?.textContent?.trim() || input.value,
        }));
      return { attributeId, label, isVariation, values };
    })
    .filter((group) => group.values.length > 0);
}

function signatureForVariant(ids) {
  return [...ids].map(Number).sort((a, b) => a - b).join(',');
}

function buildCombinationLabel(combo) {
  return combo.map((entry) => `${entry.group.label}: ${entry.value.label}`).join(' | ');
}

function describeVariantFromIds(ids) {
  if (!Array.isArray(ids) || !ids.length) return '';
  const labels = ids
    .map((id) => document.querySelector(`input[name^="attribute_values"][value="${id}"]`)?.parentElement?.textContent?.trim())
    .filter(Boolean);
  return labels.join(' / ');
}

function buildVariationCombinations(groups) {
  const variationGroups = groups.filter((group) => group.isVariation);
  if (!variationGroups.length) return [];
  return variationGroups.reduce((carry, group) => {
    const next = [];
    carry.forEach((base) => {
      group.values.forEach((value) => {
        next.push([...base, { group, value }]);
      });
    });
    return next;
  }, [[]]);
}

function escapeHtml(value) {
  return String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;');
}

function renderVariations() {
  if (!variationRows || !variantsPayload) return;
  variantsPayload.value = JSON.stringify(currentVariants);
  variationRows.innerHTML = currentVariants.length
    ? currentVariants.map((variant, index) => `
      <div class="zpf-variation-row" data-variation-index="${index}">
        <div class="zpf-variation-row-head">
          <strong>${escapeHtml(variant.label || describeVariantFromIds(variant.attribute_value_ids) || `Variation ${index + 1}`)}</strong>
          <span class="zpf-note">${escapeHtml(describeVariantFromIds(variant.attribute_value_ids) || `#${index + 1}`)}</span>
        </div>
        <div class="zpf-row zpf-row-4">
          <input class="zpf-input" data-variant-field="sku" value="${escapeHtml(variant.sku || '')}" placeholder="SKU">
          <input class="zpf-input" data-variant-field="price" type="number" step="0.01" value="${escapeHtml(variant.price || '')}" placeholder="Regular price">
          <input class="zpf-input" data-variant-field="sale_price" type="number" step="0.01" value="${escapeHtml(variant.sale_price || '')}" placeholder="Sale price">
          <input class="zpf-input" data-variant-field="stock" type="number" value="${escapeHtml(variant.stock || 0)}" placeholder="Stock">
        </div>
      </div>
    `).join('')
    : '<p class="zpf-note">No variations generated yet.</p>';
}

function hydrateGeneratedVariations() {
  const groups = getSelectedAttributeGroups();
  const combinations = buildVariationCombinations(groups);
  if (!combinations.length) {
    renderVariations();
    return;
  }
  const existingBySignature = new Map(
    currentVariants.map((variant) => [signatureForVariant(variant.attribute_value_ids || []), variant]),
  );

  currentVariants = combinations.map((combo, index) => {
    const ids = combo.map((entry) => entry.value.id);
    const signature = signatureForVariant(ids);
    const existing = existingBySignature.get(signature) || {};
    return {
      id: existing.id || null,
      label: buildCombinationLabel(combo),
      sku: existing.sku || '',
      price: existing.price ?? '',
      sale_price: existing.sale_price ?? '',
      sale_starts_at: existing.sale_starts_at || '',
      sale_ends_at: existing.sale_ends_at || '',
      stock: existing.stock ?? 0,
      low_stock_threshold: existing.low_stock_threshold ?? 5,
      attribute_value_ids: ids,
      sort_order: index,
    };
  });
  renderVariations();
}

generateVariationsBtn?.addEventListener('click', hydrateGeneratedVariations);

variationRows?.addEventListener('input', (event) => {
  const field = event.target?.getAttribute?.('data-variant-field');
  const row = event.target?.closest?.('[data-variation-index]');
  if (!field || !row) return;
  const index = Number(row.getAttribute('data-variation-index'));
  if (!currentVariants[index]) return;
  currentVariants[index][field] = event.target.value;
  variantsPayload.value = JSON.stringify(currentVariants);
});

document.querySelectorAll('[data-submit-action]').forEach((button) => {
  button.addEventListener('click', () => {
    if (submitActionInput) {
      submitActionInput.value = button.getAttribute('data-submit-action') || 'publish';
    }
  });
});

initRelatedProductFields();
initAttributeManager();

function initProductEditors(retry = 0) {
  if (!window.initBrandedRichText || !productForm) return;
  const hasSummernote = !!(window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.summernote === 'function');
  if (!hasSummernote) {
    if (retry < 30) {
      window.setTimeout(() => initProductEditors(retry + 1), 100);
    }
    return;
  }

  window.initBrandedRichText({
    selector: '#description,#short_description',
    minHeight: 280,
    toolbar: [
      ['font', ['bold', 'underline', 'italic', 'clear']],
      ['para', ['ul', 'ol', 'paragraph']],
      ['style', ['style']],
      ['color', ['color']],
      ['table', ['table']],
      ['insert', ['link', 'picture', 'video']],
      ['view', ['fullscreen', 'undo', 'redo']],
    ],
  });
}

initProductEditors();

productForm?.addEventListener('submit', () => {
  const thumbInput = document.getElementById('thumbnail_media_id');
  const galleryInput = document.getElementById('gallery_media_ids');
  const thumbPreview = document.getElementById('thumbnailMediaPreview');
  const galleryPreview = document.getElementById('galleryMediaPreview');

  if (thumbInput) {
    const thumbIds = Array.from(thumbPreview?.querySelectorAll('[data-media-id]') || [])
      .map((el) => Number(el.getAttribute('data-media-id')))
      .filter(Boolean);
    if (!String(thumbInput.value || '').trim() && thumbIds.length) {
      thumbInput.value = String(thumbIds[0]);
    }
    if (removeThumbnailInput && thumbIds.length) {
      removeThumbnailInput.value = '0';
    }
    if (removeThumbnailInput && !thumbIds.length && !String(thumbInput.value || '').trim()) {
      removeThumbnailInput.value = '1';
    }
  }

  if (galleryInput) {
    const galleryIds = Array.from(galleryPreview?.querySelectorAll('[data-media-id]') || [])
      .map((el) => Number(el.getAttribute('data-media-id')))
      .filter(Boolean);
    if (!String(galleryInput.value || '').trim() && galleryIds.length) {
      galleryInput.value = galleryIds.join(',');
    }
    if (deleteExistingGalleryInput) {
      deleteExistingGalleryInput.value = galleryIds.length ? '1' : '0';
    }
  }

  if (typeSelect?.value === 'variable' && variantsPayload) {
    variantsPayload.value = JSON.stringify(currentVariants);
  }
});

const thumbnailTrigger = document.querySelector('[data-input="#thumbnail_media_id"][data-toggle="media-picker"]');
const galleryTrigger = document.querySelector('[data-input="#gallery_media_ids"][data-toggle="media-picker"]');
const thumbnailPreview = document.getElementById('thumbnailMediaPreview');
const galleryPreview = document.getElementById('galleryMediaPreview');
const thumbnailHidden = document.getElementById('thumbnail_media_id');
const galleryHidden = document.getElementById('gallery_media_ids');
const removeThumbnailInput = document.getElementById('remove_thumbnail');
const deleteExistingGalleryInput = document.getElementById('delete_existing_gallery');

thumbnailPreview?.addEventListener('click', (e) => {
  if (e.target.closest('.zpf-wp-remove-link')) return;
  if (e.target.closest('.zpf-wp-image-main')) {
    thumbnailTrigger?.click();
  }
});

galleryPreview?.addEventListener('click', (e) => {
  if (e.target.closest('.zpf-wp-gallery-thumb')) {
    galleryTrigger?.click();
  }
});

thumbnailPreview?.addEventListener('click', (e) => {
  const removeBtn = e.target.closest('.zpf-wp-remove-link');
  if (!removeBtn) return;
  if (thumbnailHidden) {
    thumbnailHidden.value = '';
    thumbnailHidden.dispatchEvent(new Event('input', { bubbles: true }));
    thumbnailHidden.dispatchEvent(new Event('change', { bubbles: true }));
  }
  thumbnailPreview.innerHTML = '';
  if (removeThumbnailInput) {
    removeThumbnailInput.value = '1';
  }
  if (thumbnailTrigger) {
    thumbnailTrigger.style.display = '';
  }
});

document.getElementById('btn-generate-sku')?.addEventListener('click', async () => {
  const categoryId = getCategoryValue();
  const res = await fetch('/admin/products/generate-sku', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
    body: JSON.stringify({ category_id: categoryId }),
  });
  const data = await res.json();
  if (data.sku) document.getElementById('sku').value = data.sku;
});

async function aiGenerate(target = 'all') {
  const btn = target === 'seo' ? document.getElementById('btn-ai-seo') : document.getElementById('btn-ai-generate');
  if (!btn) return;
  const original = btn.textContent;
  btn.textContent = 'Generating...';
  btn.disabled = true;

  try {
    const payload = {
      name: nameInput?.value || '',
      category: getCategoryLabel(),
      keywords: document.getElementById('meta_keywords')?.value || '',
      type: typeSelect?.value || 'simple',
    };
    const res = await fetch('/admin/products/ai-generate', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
      body: JSON.stringify(payload),
    });
    const data = await res.json();
    if (data.success && data.data) {
      if (target === 'all') {
        const desc = document.getElementById('description');
        const shortDesc = document.getElementById('short_description');
        if (window.jQuery?.fn?.summernote && window.jQuery(desc).next('.note-editor').length) {
          window.jQuery(desc).summernote('code', data.data.description || '');
        } else if (desc) {
          desc.value = data.data.description || '';
        }
        if (window.jQuery?.fn?.summernote && window.jQuery(shortDesc).next('.note-editor').length) {
          window.jQuery(shortDesc).summernote('code', data.data.short_description || '');
        } else if (shortDesc) {
          shortDesc.value = data.data.short_description || '';
        }
      }
      document.getElementById('meta_title').value = data.data.meta_title || '';
      document.getElementById('meta_description').value = data.data.meta_description || '';
      document.getElementById('meta_keywords').value = data.data.meta_keywords || '';
    } else {
      alert(data.message || 'AI generation failed.');
    }
  } catch {
    alert('AI generation failed.');
  } finally {
    btn.textContent = original;
    btn.disabled = false;
  }
}

document.getElementById('btn-ai-generate')?.addEventListener('click', () => aiGenerate('all'));
document.getElementById('btn-ai-seo')?.addEventListener('click', () => aiGenerate('seo'));

if (typeSelect?.value === 'variable') {
  if (currentVariants.length) {
    renderVariations();
  } else {
    hydrateGeneratedVariations();
  }
} else {
  renderVariations();
}

updatePermalinkUI();
