@extends('admin.layouts.app')
@section('title','Products')
@section('breadcrumb','')
@push('styles')
<style>
  .admin-breadcrumb,
  .admin-page-header { display: none !important; }
  .admin-content { padding-top: 16px !important; }
</style>
@endpush
@section('content')
<section class="zprod" id="productsAjaxRoot">
  <article class="zprod-shell zprod-shell-top">
    <div class="zprod-topline">
      <h2>Products</h2>
      <div class="zprod-top-actions">
        <div class="zprod-screen-wrap">
          <button type="button" class="zprod-btn zprod-btn-tab" id="productsScreenOptionsBtn" aria-expanded="false">Screen Options</button>
          <div class="zprod-screen-options" id="productsScreenOptions" hidden>
            <strong>Columns</strong>
            <label><input type="checkbox" value="col-sku" checked> SKU</label>
            <label><input type="checkbox" value="col-stock" checked> Stock</label>
            <label><input type="checkbox" value="col-price" checked> Price</label>
            <label><input type="checkbox" value="col-category" checked> Categories</label>
            <label><input type="checkbox" value="col-date" checked> Date</label>
            <label><input type="checkbox" value="col-seo" checked> SEO details</label>
            <label><input type="checkbox" value="col-brand" checked> Brands</label>
            <span class="zprod-screen-divider"></span>
            <label class="zprod-screen-per-page">
              Number of items per page:
              <select name="per_page" class="zprod-input" form="productsFilterForm">
                <option value="10" @selected((int)($perPage ?? 20) === 10)>10</option>
                <option value="20" @selected((int)($perPage ?? 20) === 20)>20</option>
                <option value="50" @selected((int)($perPage ?? 20) === 50)>50</option>
                <option value="100" @selected((int)($perPage ?? 20) === 100)>100</option>
              </select>
            </label>
            <button type="button" class="zprod-btn zprod-btn-apply" id="productsScreenOptionsApply">Apply</button>
          </div>
        </div>
        <a href="{{ route('admin.products.create') }}" class="zprod-btn zprod-btn-primary">Add new product</a>
        <button type="button" class="zprod-btn" id="productsImportBtn">Import</button>
        <a href="{{ route('admin.products.export') }}" class="zprod-btn">Export</a>
        <form id="productsImportForm" action="{{ route('admin.products.bulk-import') }}" method="POST" enctype="multipart/form-data" style="display:none !important;">
          @csrf
          <input type="file" id="productsImportFile" name="file" accept=".csv,text/csv" hidden aria-hidden="true" tabindex="-1">
        </form>
      </div>
    </div>
    <div class="zprod-subnav" id="productsListCounts">
      <a href="{{ route('admin.products.index') }}" class="{{ empty($filters['status']) ? 'is-active' : '' }}">All ({{ $listCounts['all'] ?? 0 }})</a>
      <span>|</span>
      <a href="{{ route('admin.products.index', array_merge($filters, ['status' => 'published'])) }}" class="{{ ($filters['status'] ?? '') === 'published' ? 'is-active' : '' }}">Published ({{ $listCounts['published'] ?? 0 }})</a>
      <span>|</span>
      <a href="{{ route('admin.products.index', array_merge($filters, ['status' => 'draft'])) }}" class="{{ ($filters['status'] ?? '') === 'draft' ? 'is-active' : '' }}">Draft ({{ $listCounts['draft'] ?? 0 }})</a>
    </div>
    <form method="GET" class="zprod-filters" id="productsFilterForm">
      <div class="zprod-tools-row">
        <select name="bulk_action" class="zprod-input">
          <option value="">Bulk actions</option>
          <option value="publish">Publish</option>
          <option value="unpublish">Unpublish</option>
          <option value="feature">Mark featured</option>
          <option value="unfeatured">Remove featured</option>
          <option value="delete">Move to trash</option>
        </select>
        <button type="button" class="zprod-btn zprod-btn-apply" id="productsBulkApply">Apply</button>
        <select name="category" class="zprod-input">
          <option value="" @selected(empty($filters['category']))>Select a category</option>
          @foreach($categories as $c)
            <option value="{{ $c->id }}" @selected((string)($filters['category'] ?? '') === (string)$c->id)>{{ $c->name }}</option>
          @endforeach
        </select>
        <select name="type" class="zprod-input">
          <option value="" @selected(empty($filters['type']))>Filter by product type</option>
          <option value="simple" @selected(($filters['type'] ?? '') === 'simple')>Simple product</option>
          <option value="variable" @selected(($filters['type'] ?? '') === 'variable')>Variable product</option>
          <option value="digital" @selected(($filters['type'] ?? '') === 'digital')>Digital product</option>
          <option value="classified" @selected(($filters['type'] ?? '') === 'classified')>Classified</option>
        </select>
        <select name="stock_status" class="zprod-input">
          <option value="" @selected(empty($filters['stock_status']))>Filter by stock status</option>
          <option value="in" @selected(($filters['stock_status'] ?? '') === 'in')>In stock</option>
          <option value="out" @selected(($filters['stock_status'] ?? '') === 'out')>Out of stock</option>
        </select>
        <select name="brand" class="zprod-input">
          <option value="" @selected(empty($filters['brand']))>Filter by brand</option>
          @foreach($brands as $b)
            <option value="{{ $b->id }}" @selected((string)($filters['brand'] ?? '') === (string)$b->id)>{{ $b->name }}</option>
          @endforeach
        </select>
        <select name="sort" class="zprod-input">
          <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>Newest</option>
          <option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>Oldest</option>
          <option value="name" @selected(($filters['sort'] ?? '') === 'name')>Name</option>
          <option value="sales" @selected(($filters['sort'] ?? '') === 'sales')>Sales</option>
          <option value="stock" @selected(($filters['sort'] ?? '') === 'stock')>Stock</option>
          <option value="price" @selected(($filters['sort'] ?? '') === 'price')>Price</option>
        </select>
        <button class="zprod-btn zprod-btn-filter" type="submit">Filter</button>
      </div>
      <div class="zprod-search-row">
        <label class="zprod-search">
          <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search products" class="zprod-input">
          <button class="zprod-btn" type="submit">Search products</button>
        </label>
      </div>
    </form>
  </article>
  <article class="zprod-shell zprod-table-shell" id="productsTableShell">
    <div class="zprod-table-wrap">
      <table class="zprod-table">
        <thead>
          <tr>
            <th class="zprod-col-check"><input type="checkbox" id="productsCheckAll"></th>
            <th class="col-name">Name</th>
            <th class="col-sku">SKU</th>
            <th class="col-stock">Stock</th>
            <th class="col-price">Price</th>
            <th class="col-category">Categories</th>
            <th class="col-date">Date</th>
            <th class="col-seo">SEO details</th>
            <th class="col-brand">Brands</th>
          </tr>
        </thead>
        <tbody>
        @forelse($products as $product)
            @php
              $stockTotal = (int) $product->variants->sum('stock');
              $inStock = $stockTotal > 0;
              $variantPrice = (float) ($product->variants->min('price') ?? 0);
              $variantSale = (float) ($product->variants->whereNotNull('sale_price')->min('sale_price') ?? 0);
              $finalPrice = $variantSale > 0 ? $variantSale : $variantPrice;
            @endphp
            <tr>
              <td class="zprod-col-check"><input type="checkbox" class="zprod-row-check" value="{{ $product->id }}"></td>
              <td class="zprod-cell-product">
                <div class="zprod-product-wrap">
                  <div class="zprod-thumb">{{ strtoupper(substr($product->name, 0, 1)) }}</div>
                  <div>
                    <strong>{{ $product->name }}</strong>
                    <small>
                      <a class="zprod-edit-link" href="{{ route('admin.products.edit',$product->id) }}">Edit</a>
                      <span>|</span>
                      <a class="zprod-edit-link" href="{{ route('product.show', $product->slug) }}" target="_blank" rel="noopener">View</a>
                      <span>|</span>
                      {!! $product->is_published ? '<span class="zprod-mini-status is-published">Published</span>' : '<span class="zprod-mini-status is-draft">Draft</span>' !!}
                    </small>
                  </div>
                </div>
              </td>
              <td class="col-sku">{{ $product->sku ?: '—' }}</td>
              <td class="col-stock {{ $inStock ? 'zprod-stock-in' : 'zprod-stock-out' }}">{{ $inStock ? 'In stock' : 'Out of stock' }} @if($inStock) ({{ $stockTotal }}) @endif</td>
              <td class="col-price">
                @if($variantSale > 0 && $variantSale < $variantPrice)
                  <span class="zprod-price-old">{{ currency_format($variantPrice) }}</span>
                @endif
                <span class="zprod-price-main">{{ currency_format($finalPrice) }}</span>
              </td>
              <td class="col-category">{{ $product->category?->name ?: 'Uncategorized' }}</td>
              <td class="col-date">
                <span class="zprod-date-pill">{{ $product->is_published ? 'Published' : 'Draft' }}</span>
                <small>@datetime($product->created_at)</small>
              </td>
              <td class="col-seo">
                <span class="zprod-seo-score">{{ $product->meta_title ? '82 / 100' : 'N/A' }}</span>
                <small>Schema: WooCommerce Product</small>
              </td>
              <td class="col-brand">{{ $product->brand?->name ?: '—' }}</td>
            </tr>
        @empty
            <tr class="zprod-empty-row">
              <td colspan="10">
                <div class="zprod-empty">
                  <strong>No products found</strong>
                  <p>Try changing filters or add a new product.</p>
                </div>
              </td>
            </tr>
        @endforelse
        </tbody>
      </table>
    </div>
    <div class="zprod-footer-tools">
      <div class="zprod-footer-left">
        <select name="bulk_action_bottom" class="zprod-input">
          <option value="">Bulk actions</option>
          <option value="publish">Publish</option>
          <option value="unpublish">Unpublish</option>
          <option value="feature">Mark featured</option>
          <option value="unfeatured">Remove featured</option>
          <option value="delete">Move to trash</option>
        </select>
        <button type="button" class="zprod-btn zprod-btn-apply" id="productsBulkApplyBottom">Apply</button>
      </div>
      <div class="zprod-footer-right">{{ $products->total() }} items</div>
    </div>
  </article>
  <div class="pt-1" id="productsPagination">{{ $products->links() }}</div>
</section>
@push('scripts')
<script>
(() => {
  const root = document.getElementById('productsAjaxRoot');
  const form = document.getElementById('productsFilterForm');
  const tableShell = document.getElementById('productsTableShell');
  const pagination = document.getElementById('productsPagination');
  const screenBtn = document.getElementById('productsScreenOptionsBtn');
  const screenPanel = document.getElementById('productsScreenOptions');
  const screenApplyBtn = document.getElementById('productsScreenOptionsApply');
  const importBtn = document.getElementById('productsImportBtn');
  const importForm = document.getElementById('productsImportForm');
  const importFile = document.getElementById('productsImportFile');
  const STORAGE_KEY = 'admin_products_visible_columns_v1';
  if (!root || !form || !tableShell || !pagination) return;

  const searchInput = form.querySelector('input[name="search"]');
  let debounceId = null;
  let activeController = null;
  let bulkBusy = false;

  const setLoading = (loading) => {
    tableShell.classList.toggle('is-loading', loading);
    form.classList.toggle('is-loading', loading);
  };

  const replaceFromResponse = (html) => {
    const doc = new DOMParser().parseFromString(html, 'text/html');
    const nextShell = doc.getElementById('productsTableShell');
    const nextPagination = doc.getElementById('productsPagination');
    const nextForm = doc.getElementById('productsFilterForm');
    const nextCounts = doc.getElementById('productsListCounts');
    const counts = document.getElementById('productsListCounts');
    if (!nextShell || !nextPagination || !nextForm) return false;
    tableShell.innerHTML = nextShell.innerHTML;
    pagination.innerHTML = nextPagination.innerHTML;
    form.innerHTML = nextForm.innerHTML;
    if (nextCounts && counts) counts.innerHTML = nextCounts.innerHTML;
    applyVisibleColumns();
    return true;
  };

  const defaultColumns = ['col-sku','col-stock','col-price','col-category','col-date','col-seo','col-brand'];
  const loadVisibleColumns = () => {
    try {
      const parsed = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
      if (Array.isArray(parsed) && parsed.length) return parsed;
    } catch (e) {}
    return [...defaultColumns];
  };

  const saveVisibleColumns = (cols) => {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(cols));
  };

  const applyVisibleColumns = () => {
    const visible = new Set(loadVisibleColumns());
    defaultColumns.forEach((col) => {
      root.querySelectorAll('.' + col).forEach((node) => {
        node.classList.toggle('is-hidden-col', !visible.has(col));
      });
    });
    if (screenPanel) {
      screenPanel.querySelectorAll('input[type="checkbox"]').forEach((input) => {
        input.checked = visible.has(input.value);
      });
    }
  };

  const fetchProducts = async (url) => {
    if (activeController) activeController.abort();
    activeController = new AbortController();
    setLoading(true);
    try {
      const res = await fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        signal: activeController.signal,
      });
      if (!res.ok) return;
      const html = await res.text();
      if (!replaceFromResponse(html)) return;
      window.history.replaceState({}, '', url);
    } catch (e) {
      if (e.name !== 'AbortError') {}
    } finally {
      setLoading(false);
    }
  };

  const buildUrlFromForm = () => {
    const data = new FormData(form);
    const params = new URLSearchParams();
    for (const [key, value] of data.entries()) {
      if ((value ?? '').toString().trim() !== '') params.append(key, value);
    }
    return `${window.location.pathname}${params.toString() ? `?${params.toString()}` : ''}`;
  };

  document.addEventListener('submit', (e) => {
    const target = e.target;
    if (!(target instanceof HTMLFormElement) || target.id !== 'productsFilterForm') return;
    e.preventDefault();
    fetchProducts(buildUrlFromForm());
  });

  root.addEventListener('click', (e) => {
    const target = e.target;
    if (!(target instanceof Element)) return;
    const link = target.closest('#productsPagination a');
    if (!link) return;
    e.preventDefault();
    const url = link.getAttribute('href');
    if (url) fetchProducts(url);
  });

  root.addEventListener('change', (e) => {
    const target = e.target;
    if (!(target instanceof HTMLSelectElement) && !(target instanceof HTMLInputElement)) return;
    if (target.id === 'productsCheckAll') {
      root.querySelectorAll('.zprod-row-check').forEach((box) => {
        box.checked = target.checked;
      });
      return;
    }
    if (!target.closest('#productsFilterForm')) return;
    if (target.name === 'bulk_action') return;
    fetchProducts(buildUrlFromForm());
  });

  root.addEventListener('input', (e) => {
    const target = e.target;
    if (!(target instanceof HTMLInputElement)) return;
    if (target.name !== 'search') return;
    if (debounceId) clearTimeout(debounceId);
    debounceId = setTimeout(() => fetchProducts(buildUrlFromForm()), 280);
  });

  if (searchInput instanceof HTMLInputElement) {
    searchInput.setAttribute('autocomplete', 'off');
  }

  if (screenBtn && screenPanel) {
    screenPanel.hidden = true;
    screenBtn.setAttribute('aria-expanded', 'false');
    screenBtn.addEventListener('click', () => {
      const isOpen = !screenPanel.hidden;
      screenPanel.hidden = isOpen;
      screenBtn.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
    });
    document.addEventListener('click', (e) => {
      if (!screenPanel.contains(e.target) && !screenBtn.contains(e.target)) {
        screenPanel.hidden = true;
        screenBtn.setAttribute('aria-expanded', 'false');
      }
    });
    screenPanel.addEventListener('change', () => {
      const selected = Array.from(screenPanel.querySelectorAll('input[type="checkbox"]:checked')).map((i) => i.value);
      if (!selected.length) {
        const first = screenPanel.querySelector('input[type="checkbox"]');
        if (first) first.checked = true;
      }
    });
  }

  if (screenApplyBtn) {
    screenApplyBtn.addEventListener('click', () => {
      const selected = Array.from(screenPanel.querySelectorAll('input[type="checkbox"]:checked')).map((i) => i.value);
      if (!selected.length) return;
      saveVisibleColumns(selected);
      applyVisibleColumns();
      fetchProducts(buildUrlFromForm());
      screenPanel.hidden = true;
      if (screenBtn) screenBtn.setAttribute('aria-expanded', 'false');
    });
  }

  if (importBtn && importForm && importFile) {
    importBtn.addEventListener('click', () => {
      importFile.click();
    });
    importFile.addEventListener('change', async () => {
      const selected = importFile.files?.[0];
      if (!selected) return;
      const body = new FormData(importForm);
      body.set('file', selected);
      setLoading(true);
      try {
        const res = await fetch(importForm.action, {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body,
        });
        if (res.ok) {
          await fetchProducts(buildUrlFromForm());
        }
      } catch (e) {
      } finally {
        setLoading(false);
        importFile.value = '';
      }
    });
  }

  const getCheckedIds = () => {
    return Array.from(root.querySelectorAll('.zprod-row-check:checked')).map((el) => Number(el.value)).filter(Boolean);
  };

  const runBulkAction = async (source = 'top') => {
    if (bulkBusy) return;
    const selectName = source === 'bottom' ? 'bulk_action_bottom' : 'bulk_action';
    const action = form.querySelector(`select[name="${selectName}"]`)?.value || '';
    const ids = getCheckedIds();
    if (!action || !ids.length) return;
    bulkBusy = true;
    setLoading(true);
    try {
      await fetch(`{{ route('admin.products.bulk-action') }}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ action, ids }),
      });
      fetchProducts(buildUrlFromForm());
    } catch (e) {
      setLoading(false);
    } finally {
      bulkBusy = false;
    }
  };

  root.addEventListener('click', (e) => {
    const target = e.target;
    if (!(target instanceof Element)) return;
    if (target.id === 'productsBulkApply') {
      e.preventDefault();
      runBulkAction('top');
    }
    if (target.id === 'productsBulkApplyBottom') {
      e.preventDefault();
      const topBulk = form.querySelector('select[name="bulk_action"]');
      const bottomBulk = root.querySelector('select[name="bulk_action_bottom"]');
      if (topBulk && bottomBulk) topBulk.value = bottomBulk.value;
      runBulkAction('bottom');
    }
  });

  applyVisibleColumns();
})();
</script>
@endpush
@endsection
