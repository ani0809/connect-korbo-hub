<div class="search-overlay" x-show="searchOpen" @click.self="searchOpen=false" @keydown.escape.window="searchOpen=false" x-transition.opacity>
  <div class="search-modal" x-data="LiveSearch()">
    <div class="search-input-wrapper">
      <input type="text" x-ref="searchInput" x-model.debounce.300ms="query" @input="search()" @keydown.enter="goToSearch()" class="search-main-input" placeholder="Search for products, categories, brands...">
      <button class="ui-icon-btn m-2" @click="searchOpen=false">Close</button>
    </div>

    <div class="p-4" x-show="query.length>=2">
      <div x-show="loading" class="text-sm text-slate-500">Searching...</div>
      <div x-show="!loading" class="space-y-2">
        <template x-for="s in suggestions"><div class="search-suggestion" @click="doSearch(s)" x-text="s"></div></template>
        <template x-for="cat in categories"><a :href="cat.url" class="search-category-item" x-text="cat.name"></a></template>
        <template x-for="product in products">
          <a :href="product.url" class="search-product-item">
            <img :src="product.thumbnail" class="search-prod-img">
            <span x-text="product.name"></span>
          </a>
        </template>
      </div>
    </div>

    <div x-show="query.length<2" class="search-popular p-4 border-t text-sm text-slate-600">
      Popular: <a href="{{ route('search',['q'=>'Shoes']) }}">Shoes</a>, <a href="{{ route('search',['q'=>'Electronics']) }}">Electronics</a>
    </div>
  </div>
</div>
