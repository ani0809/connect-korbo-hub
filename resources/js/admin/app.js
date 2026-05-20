import Alpine from 'alpinejs';
import './theme-settings';
import './staff-permissions';
import './feature-toggles';
import './coupon-form';
import './import-export';
import './richtext-shared';
import '../shared/media-picker';
import '../frontend/toast.js';

window.Alpine = Alpine;
Alpine.start();

function closeAllDropdowns(except) {
    document.querySelectorAll('[data-dropdown].open').forEach((root) => {
        if (except && root.contains(except)) return;
        root.classList.remove('open');
    });
}

document.addEventListener('click', (e) => {
    const trigger = e.target.closest?.('[data-dropdown-trigger]');
    if (trigger) {
        const root = trigger.closest('[data-dropdown]');
        if (root) {
            const wasOpen = root.classList.contains('open');
            closeAllDropdowns(root);
            root.classList.toggle('open', !wasOpen);
        }
        return;
    }
    closeAllDropdowns(null);
});

document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    closeAllDropdowns(null);
});

window.adminConfirm = (message, title) => {
    const text = title ? `${title}\n\n${message}` : message;
    return window.confirm(text);
};

if (window.sessionStorage?.getItem('admin_toast')) {
    try {
        const { msg, type } = JSON.parse(window.sessionStorage.getItem('admin_toast'));
        window.sessionStorage.removeItem('admin_toast');
        const fn = window.Toast?.[type];
        if (typeof fn === 'function') {
            fn(msg);
        } else {
            window.Toast?.info?.(msg);
        }
    } catch {
        /* ignore */
    }
}
