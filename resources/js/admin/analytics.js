/* global Chart */

function readData(root, camelKey) {
  try {
    const raw = root?.dataset?.[camelKey];
    return raw ? JSON.parse(raw) : [];
  } catch {
    return [];
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const root = document.getElementById('adv-analytics');
  if (!root || typeof Chart === 'undefined') return;

  const rev = readData(root, 'revenueChart');
  const revPrev = readData(root, 'revenuePrev');
  const cat = readData(root, 'category');
  const pay = readData(root, 'payments');
  const hourly = readData(root, 'hourly');

  const labels = rev.map((r) => r.date);
  const data = rev.map((r) => parseFloat(r.revenue) || 0);
  const compare = root.dataset.compare === '1';

  new Chart(document.getElementById('chart-revenue'), {
    type: 'line',
    data: {
      labels,
      datasets: [
        {
          label: 'Revenue',
          data,
          borderColor: '#187BA5',
          tension: 0.2,
        },
        ...(compare && revPrev.length
          ? [
              {
                label: 'Previous',
                data: revPrev.map((r) => parseFloat(r.revenue) || 0),
                borderColor: '#94a3b8',
                borderDash: [4, 4],
                tension: 0.2,
              },
            ]
          : []),
      ],
    },
    options: { responsive: true, plugins: { legend: { display: compare } } },
  });

  new Chart(document.getElementById('chart-category'), {
    type: 'doughnut',
    data: {
      labels: cat.map((c) => c.name),
      datasets: [{ data: cat.map((c) => parseFloat(c.revenue) || 0), backgroundColor: ['#187BA5', '#3DB5A6', '#f59e0b', '#8b5cf6', '#ec4899'] }],
    },
    options: { responsive: true },
  });

  new Chart(document.getElementById('chart-pay'), {
    type: 'bar',
    data: {
      labels: pay.map((p) => p.payment_method),
      datasets: [{ label: 'Revenue', data: pay.map((p) => parseFloat(p.revenue) || 0), backgroundColor: '#187BA5' }],
    },
    options: { responsive: true, scales: { x: { ticks: { maxRotation: 45 } } } },
  });

  new Chart(document.getElementById('chart-hour'), {
    type: 'bar',
    data: {
      labels: hourly.map((h) => `${h.hour}:00`),
      datasets: [{ label: 'Orders', data: hourly.map((h) => parseInt(h.orders, 10) || 0), backgroundColor: '#3DB5A6' }],
    },
    options: { responsive: true },
  });

  setInterval(() => {
    fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(() => {})
      .catch(() => {});
    const el = document.getElementById('rt-visitors');
    if (el) {
      el.textContent = String(parseInt(el.textContent, 10) || 0);
    }
  }, 30000);
});
