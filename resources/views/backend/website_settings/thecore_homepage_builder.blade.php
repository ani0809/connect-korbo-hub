@extends('backend.layouts.app')

@section('content')
<div class="cibato-titlebar text-left mt-2 mb-3 pb-2 border-bottom border-gray">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3">{{ translate('Thecore Homepage Builder') }}</h1>
            <p class="text-muted mb-0">{{ translate('Drag-drop sections, toggle visibility, duplicate, and save layout order.') }}</p>
        </div>
    </div>
</div>

<div class="card rounded-0">
    <div class="card-body p-4">
        <form id="thecore-layout-form" action="{{ route('website.thecore-homepage-builder.save') }}" method="POST">
            @csrf
            <input type="hidden" name="layout" id="thecore-layout-json">

            <div class="mb-3">
                <ul class="list-group" id="layout-builder-list">
                    @foreach ($layoutItems as $item)
                        @php
                            $sectionTitle = $availableSections[$item['section_key']] ?? $item['section_key'];
                            $isAvailable = !array_key_exists($item['section_key'], $sectionAvailability ?? []) || ($sectionAvailability[$item['section_key']] ?? true);
                        @endphp
                        <li class="list-group-item d-flex align-items-center justify-content-between builder-item" draggable="true"
                            data-item-id="{{ $item['id'] }}"
                            data-section-key="{{ $item['section_key'] }}"
                            data-enabled="{{ $item['enabled'] ? '1' : '0' }}"
                            data-settings='@json($item["settings"] ?? [])'>
                            <div class="d-flex align-items-center">
                                <span class="mr-3 cursor-move"><i class="las la-grip-vertical fs-20"></i></span>
                                <div>
                                    <div class="fw-600 section-label">{{ $sectionTitle }}</div>
                                    <small class="text-muted section-key">{{ $item['section_key'] }}</small>
                                    @if (!$isAvailable)
                                        <small class="d-block text-warning">{{ translate('Currently unavailable in frontend due to addon/setting') }}</small>
                                    @endif
                                    @if ($item['section_key'] === 'home_categories')
                                        @php $selectedCategoryId = (int) ($item['settings']['category_id'] ?? 0); @endphp
                                        <div class="mt-2">
                                            <select class="form-control form-control-sm home-category-selector" style="min-width: 220px;" {{ !$isAvailable ? 'disabled' : '' }}>
                                                <option value="">{{ translate('Select category') }}</option>
                                                @foreach ($homeCategories as $category)
                                                    <option value="{{ $category->id }}" {{ $selectedCategoryId === (int) $category->id ? 'selected' : '' }}>
                                                        {{ $category->getTranslation('name') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mt-2">
                                            <input type="text"
                                                class="form-control form-control-sm home-category-custom-title"
                                                placeholder="{{ translate('Custom title (optional)') }}"
                                                value="{{ $item['settings']['custom_title'] ?? '' }}"
                                                {{ !$isAvailable ? 'disabled' : '' }}>
                                        </div>
                                    @endif
                                    @if ($item['section_key'] === 'newest_products')
                                        <div class="mt-2">
                                            <input type="text"
                                                class="form-control form-control-sm newest-products-custom-title"
                                                placeholder="{{ translate('Custom title (optional)') }}"
                                                value="{{ $item['settings']['custom_title'] ?? '' }}"
                                                {{ !$isAvailable ? 'disabled' : '' }}>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <label class="cibato-switch cibato-switch-success mb-0 mr-2">
                                    <input type="checkbox" class="section-enabled-toggle" {{ $item['enabled'] ? 'checked' : '' }} {{ !$isAvailable ? 'disabled' : '' }}>
                                    <span></span>
                                </label>
                                <button type="button" class="btn btn-soft-info btn-icon btn-circle btn-sm mr-2 duplicate-section" title="{{ translate('Duplicate') }}">
                                    <i class="las la-copy"></i>
                                </button>
                                <button type="button" class="btn btn-soft-danger btn-icon btn-circle btn-sm remove-duplicate"
                                    title="{{ translate('Remove') }}" {{ in_array($item['section_key'], ['hero_slider', 'newest_products']) ? 'disabled' : '' }}>
                                    <i class="las la-trash"></i>
                                </button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-success mr-2">{{ translate('Save Layout') }}</button>
            </div>
        </form>

        <form action="{{ route('website.thecore-homepage-builder.reset') }}" method="POST" class="text-right mt-2">
            @csrf
            <button type="submit" class="btn btn-soft-secondary">{{ translate('Reset to Default') }}</button>
        </form>
    </div>
</div>
@endsection

@section('style')
<style>
    #layout-builder-list .builder-item {
        transition: transform .15s ease, box-shadow .15s ease, background-color .15s ease;
    }
    #layout-builder-list .builder-item.dragging {
        opacity: .9;
        transform: scale(1.01);
        box-shadow: 0 8px 20px rgba(0, 0, 0, .08);
        background-color: #f7f8fa;
    }
    #layout-builder-list .drop-placeholder {
        border: 2px dashed #4d7cfe;
        border-radius: 6px;
        height: 58px;
        margin: 4px 0;
        background: rgba(77, 124, 254, 0.06);
    }
    #layout-builder-list .cursor-move {
        cursor: grab;
    }
    #layout-builder-list .cursor-move:active {
        cursor: grabbing;
    }
</style>
@endsection

@section('script')
<script>
    (function() {
        const builderList = document.getElementById('layout-builder-list');
        const layoutInput = document.getElementById('thecore-layout-json');
        const form = document.getElementById('thecore-layout-form');
        let dragItem = null;
        let dragHandleActive = false;
        let dragPlaceholder = null;

        function uniqueId() {
            return 'thc-' + Math.random().toString(36).slice(2, 12);
        }

        function serializeLayout() {
            const items = [];
            builderList.querySelectorAll('.builder-item').forEach((el) => {
                const settings = {};
                if (el.dataset.sectionKey === 'home_categories') {
                    const selector = el.querySelector('.home-category-selector');
                    if (selector && selector.value) {
                        settings.category_id = Number(selector.value);
                    }
                    const titleInput = el.querySelector('.home-category-custom-title');
                    if (titleInput && titleInput.value.trim() !== '') {
                        settings.custom_title = titleInput.value.trim();
                    }
                } else if (el.dataset.sectionKey === 'newest_products') {
                    const titleInput = el.querySelector('.newest-products-custom-title');
                    if (titleInput) {
                        settings.custom_title = titleInput.value.trim();
                    }
                } else {
                    try {
                        const parsed = JSON.parse(el.dataset.settings || '{}');
                        if (parsed && typeof parsed === 'object') {
                            Object.assign(settings, parsed);
                        }
                    } catch (error) {}
                }
                items.push({
                    id: el.dataset.itemId,
                    section_key: el.dataset.sectionKey,
                    enabled: el.dataset.enabled === '1',
                    settings: settings
                });
            });
            return items;
        }

        function syncLayoutInput() {
            layoutInput.value = JSON.stringify(serializeLayout());
        }

        function clearPlaceholder() {
            if (dragPlaceholder && dragPlaceholder.parentNode) {
                dragPlaceholder.parentNode.removeChild(dragPlaceholder);
            }
            dragPlaceholder = null;
        }

        builderList.addEventListener('change', function(e) {
            if (e.target.classList.contains('section-enabled-toggle')) {
                const item = e.target.closest('.builder-item');
                item.dataset.enabled = e.target.checked ? '1' : '0';
                syncLayoutInput();
            }
            if (e.target.classList.contains('home-category-selector')) {
                syncLayoutInput();
            }
            if (e.target.classList.contains('home-category-custom-title')) {
                syncLayoutInput();
            }
            if (e.target.classList.contains('newest-products-custom-title')) {
                syncLayoutInput();
            }
        });

        builderList.addEventListener('click', function(e) {
            const duplicateBtn = e.target.closest('.duplicate-section');
            if (duplicateBtn) {
                const currentItem = duplicateBtn.closest('.builder-item');
                const cloned = currentItem.cloneNode(true);
                cloned.dataset.itemId = uniqueId();
                cloned.querySelector('.remove-duplicate').disabled = false;
                const title = cloned.querySelector('.section-label');
                title.textContent = title.textContent + ' (Copy)';
                cloned.dataset.settings = currentItem.dataset.settings || '{}';
                currentItem.insertAdjacentElement('afterend', cloned);
                syncLayoutInput();
                return;
            }

            const removeBtn = e.target.closest('.remove-duplicate');
            if (removeBtn && !removeBtn.disabled) {
                removeBtn.closest('.builder-item').remove();
                syncLayoutInput();
            }
        });

        builderList.addEventListener('mousedown', function(e) {
            dragHandleActive = !!e.target.closest('.cursor-move');
        });

        builderList.addEventListener('mouseup', function() {
            dragHandleActive = false;
        });

        builderList.addEventListener('dragstart', function(e) {
            const target = e.target.closest('.builder-item');
            if (!target) return;

             if (!dragHandleActive) {
                e.preventDefault();
                return;
            }

            dragItem = target;
            target.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', target.dataset.itemId);

            dragPlaceholder = document.createElement('li');
            dragPlaceholder.className = 'drop-placeholder';
            dragPlaceholder.setAttribute('aria-hidden', 'true');
            target.after(dragPlaceholder);

            requestAnimationFrame(() => {
                target.style.display = 'none';
            });
        });

        builderList.addEventListener('dragover', function(e) {
            e.preventDefault();
            const target = e.target.closest('.builder-item');
            if (!target || !dragItem || target === dragItem || !dragPlaceholder) return;
            const rect = target.getBoundingClientRect();
            const isAfter = e.clientY > rect.top + rect.height / 2;
            if (isAfter) {
                target.after(dragPlaceholder);
            } else {
                target.before(dragPlaceholder);
            }
        });

        builderList.addEventListener('drop', function(e) {
            e.preventDefault();
            if (!dragItem || !dragPlaceholder) return;

            dragItem.style.display = '';
            dragPlaceholder.replaceWith(dragItem);
            dragItem.classList.remove('dragging');
            dragItem = null;
            dragHandleActive = false;
            dragPlaceholder = null;
            syncLayoutInput();
        });

        builderList.addEventListener('dragend', function() {
            if (dragItem) {
                dragItem.style.display = '';
                dragItem.classList.remove('dragging');
                clearPlaceholder();
            }
            dragItem = null;
            dragHandleActive = false;
            syncLayoutInput();
        });

        form.addEventListener('submit', function() {
            syncLayoutInput();
        });

        syncLayoutInput();
    })();
</script>
@endsection
