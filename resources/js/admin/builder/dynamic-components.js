function currency(amount) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: window.siteConfig?.currencyCode || 'USD' }).format(Number(amount || 0));
}

export function registerDynamicComponents(editor) {
  editor.DomComponents.addType('ec-product-slider', {
    model: {
      defaults: {
        tagName: 'section',
        droppable: false,
        attributes: { class: 'ec-product-slider', 'data-limit': '8' },
        components: `
          <div style="padding:32px 0">
            <h2 style="font-size:28px;font-weight:700;margin-bottom:14px;color:#1e293b">Product Slider</h2>
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px">
              ${Array(4).fill(0).map(() => '<article style="border:1px solid #e2e8f0;border-radius:12px;background:#fff;padding:12px">Product card</article>').join('')}
            </div>
          </div>
        `,
      },
    },
  });

  editor.DomComponents.addType('ec-brands', {
    model: {
      defaults: {
        tagName: 'section',
        droppable: false,
        components: `
          <div style="padding:24px 0">
            <h3 style="font-size:22px;font-weight:700;color:#1e293b;margin-bottom:12px">Trusted Brands</h3>
            <div style="display:flex;gap:12px;flex-wrap:wrap">
              ${['Nike', 'Apple', 'Samsung', 'Sony', 'Adidas', 'Puma'].map((b) => `<span style="padding:8px 14px;border:1px solid #e2e8f0;border-radius:999px;background:#fff">${b}</span>`).join('')}
            </div>
          </div>
        `,
      },
    },
  });

  editor.DomComponents.addType('ec-flash-sale', {
    model: {
      defaults: {
        tagName: 'section',
        droppable: false,
        components: `
          <div style="padding:28px;border-radius:16px;background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff">
            <div style="font-size:13px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;margin-bottom:8px;opacity:.85">Flash Deal</div>
            <h3 style="font-size:30px;line-height:1.2;font-weight:800;margin-bottom:10px">Ends In 23:59:59</h3>
            <p style="opacity:.9">Use this block for limited offers and urgency campaigns.</p>
          </div>
        `,
      },
    },
  });

  editor.DomComponents.addType('ec-single-product', {
    model: {
      defaults: {
        tagName: 'section',
        droppable: false,
        components: `
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:center;border:1px solid #e2e8f0;border-radius:16px;background:#fff;padding:16px">
            <img src="https://placehold.co/640x640/e2e8f0/64748b?text=Featured+Product" alt="" style="width:100%;border-radius:12px;object-fit:cover">
            <div>
              <h3 style="font-size:28px;font-weight:800;color:#0f172a">Featured Product Title</h3>
              <p style="margin-top:10px;color:#475569;line-height:1.7">Highlight one flagship product with CTA, price and quick message.</p>
              <div style="margin-top:14px;font-size:26px;font-weight:800;color:#2563eb">${currency(99.99)}</div>
              <a href="/shop" style="margin-top:16px;display:inline-block;background:#2563eb;color:#fff;text-decoration:none;padding:12px 22px;border-radius:10px;font-weight:700">View Product →</a>
            </div>
          </div>
        `,
      },
    },
  });
}
