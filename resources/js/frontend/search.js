window.LiveSearch = function LiveSearch() {
  return {
    query: '',
    loading: false,
    products: [],
    categories: [],
    suggestions: [],
    total: 0,
    async search() {
      if (this.query.length < 2) { this.products = []; this.categories = []; this.suggestions = []; this.total = 0; return; }
      this.loading = true;
      try {
        const r = await fetch(`/search/suggestions?q=${encodeURIComponent(this.query)}`);
        const d = await r.json();
        this.products = d.products || [];
        this.categories = d.categories || [];
        this.suggestions = d.suggestions || [];
        this.total = d.total || 0;
      } finally { this.loading = false; }
    },
    goToSearch() { if (this.query.trim()) window.location.href = `/search?q=${encodeURIComponent(this.query)}`; },
    doSearch(term) { const q = String(term).replace(/^Search for "(.+)"$/, '$1'); window.location.href = `/search?q=${encodeURIComponent(q)}`; },
    trap(e) {
      if (!window.searchOpen) return;
      const focusables = Array.from(document.querySelectorAll('.search-modal a,.search-modal button,.search-modal input,[tabindex]:not([tabindex="-1"])')).filter((el) => !el.disabled);
      if (!focusables.length) return;
      const first = focusables[0], last = focusables[focusables.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    },
  };
};
