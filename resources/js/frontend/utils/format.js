window.formatMoney = (n) => new Intl.NumberFormat(undefined, { style: 'currency', currency: document.documentElement.dataset.currency || 'USD' }).format(Number(n || 0));
