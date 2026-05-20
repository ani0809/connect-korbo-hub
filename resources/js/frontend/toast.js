const DEFAULT_DURATION = 4000;

function ensureContainer() {
    let wrap = document.getElementById('toast-container');
    if (!wrap) {
        wrap = document.querySelector('.toast-container');
    }
    if (!wrap) {
        wrap = document.createElement('div');
        wrap.id = 'toast-container';
        wrap.className = 'toast-container';
        document.body.appendChild(wrap);
    }
    return wrap;
}

function iconSvg(type) {
    const common =
        'class="toast-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"';
    if (type === 'success') {
        return `<svg ${common} aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>`;
    }
    if (type === 'error') {
        return `<svg ${common} aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/></svg>`;
    }
    if (type === 'warning') {
        return `<svg ${common} aria-hidden="true"><path d="M12 9v4M12 17h.01"/><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>`;
    }
    return `<svg ${common} aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>`;
}

function dismissToast(el, wrap) {
    if (!el || el.dataset.dismissing === '1') return;
    el.dataset.dismissing = '1';
    el.classList.add('hiding');
    window.setTimeout(() => {
        el.remove();
        if (wrap && wrap.childElementCount === 0) {
            wrap.style.display = '';
        }
    }, 280);
}

/**
 * @param {string|{title?: string, message: string}} message
 * @param {'success'|'error'|'warning'|'info'} [type]
 * @param {number} [duration]
 */
function showToast(message, type = 'info', duration = DEFAULT_DURATION) {
    const wrap = ensureContainer();
    wrap.style.display = 'flex';

    let title = '';
    let body = '';
    if (message && typeof message === 'object' && 'message' in message) {
        title = message.title ? String(message.title) : '';
        body = String(message.message ?? '');
    } else {
        body = String(message ?? '');
    }

    const types = ['success', 'error', 'warning', 'info'];
    const t = types.includes(type) ? type : 'info';

    const el = document.createElement('div');
    el.className = `toast toast-${t}`;
    el.setAttribute('role', 'status');
    const progressHtml = duration > 0
        ? `<div class="toast-progress" style="animation-duration:${duration}ms;color:var(--color-primary)"></div>`
        : '';
    el.innerHTML = `
    ${iconSvg(t)}
    <div class="toast-content">
      ${title ? `<div class="toast-title">${escapeHtml(title)}</div>` : ''}
      <div class="toast-message">${escapeHtml(body)}</div>
    </div>
    <button type="button" class="toast-close" aria-label="Dismiss">&times;</button>
    ${progressHtml}
  `;

    const close = el.querySelector('.toast-close');
    close?.addEventListener('click', () => dismissToast(el, wrap));

    el.addEventListener('click', (e) => {
        if (e.target === el || (e.target instanceof HTMLElement && e.target.classList.contains('toast-content'))) {
            dismissToast(el, wrap);
        }
    });

    wrap.appendChild(el);

    let timer = null;
    if (duration > 0) {
        let remaining = duration;
        let startedAt = Date.now();
        timer = window.setTimeout(() => dismissToast(el, wrap), remaining);
        el.addEventListener('mouseenter', () => {
            if (!timer) return;
            window.clearTimeout(timer);
            remaining -= Date.now() - startedAt;
            remaining = Math.max(1200, remaining);
        });
        el.addEventListener('mouseleave', () => {
            startedAt = Date.now();
            timer = window.setTimeout(() => dismissToast(el, wrap), remaining);
        });
    }

    return el;
}

function escapeHtml(str) {
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

const Toast = {
    show(message, type, duration) {
        return showToast(message, type, duration ?? DEFAULT_DURATION);
    },
    success(message, duration) {
        return showToast(message, 'success', duration ?? DEFAULT_DURATION);
    },
    error(message, duration) {
        return showToast(message, 'error', duration ?? 0);
    },
    warning(message, duration) {
        return showToast(message, 'warning', duration ?? DEFAULT_DURATION);
    },
    info(message, duration) {
        return showToast(message, 'info', duration ?? DEFAULT_DURATION);
    },
};

window.Toast = Toast;
window.showToast = (message, type = 'info', duration) => Toast.show(message, type, duration);

export default Toast;
