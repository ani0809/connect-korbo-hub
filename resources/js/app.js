import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('darkMode', () => ({
        dark: localStorage.getItem('darkMode') === 'true',
        toggle() {
            this.dark = !this.dark;
            localStorage.setItem('darkMode', this.dark);
            document.documentElement.classList.toggle('dark', this.dark);
        },
        init() {
            document.documentElement.classList.toggle('dark', this.dark);
        },
    }));
});

Alpine.start();

window.toast = {
    container: null,
    getContainer() {
        if (!this.container) {
            this.container = document.getElementById('toast-container');
            if (!this.container) {
                this.container = document.createElement('div');
                this.container.id = 'toast-container';
                this.container.className = 'toast-container';
                document.body.appendChild(this.container);
            }
        }
        return this.container;
    },
    show(message, type = 'default') {
        const icons = { success: '✅', error: '❌', info: 'ℹ️', warning: '⚠️', default: '💬' };
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `<span class="toast-icon">${icons[type] || icons.default}</span><span class="toast-message">${message}</span><button class="toast-close" onclick="this.parentElement.remove()">×</button>`;
        this.getContainer().appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(20px)';
            toast.style.transition = '0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    },
    success(msg) { this.show(msg, 'success'); },
    error(msg) { this.show(msg, 'error'); },
    info(msg) { this.show(msg, 'info'); },
    warning(msg) { this.show(msg, 'warning'); },
};

window.flyToCart = function flyToCart(buttonEl) {
    const cartIcon = document.querySelector('.header-cart-icon');
    if (!buttonEl || !cartIcon) return;
    const startRect = buttonEl.getBoundingClientRect();
    const endRect = cartIcon.getBoundingClientRect();
    const fly = document.createElement('div');
    fly.style.cssText = `position:fixed;top:${startRect.top}px;left:${startRect.left}px;width:${startRect.width}px;height:${startRect.height}px;background:var(--gradient-primary);border-radius:var(--radius-lg);z-index:9999;pointer-events:none;opacity:.8;`;
    document.body.appendChild(fly);
    const dx = endRect.left - startRect.left;
    const dy = endRect.top - startRect.top;
    fly.animate(
        [{ transform: 'scale(1) translate(0, 0)', opacity: 0.8 }, { transform: `scale(0.2) translate(${dx}px, ${dy}px)`, opacity: 0 }],
        { duration: 700, easing: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)', fill: 'forwards' },
    ).onfinish = () => {
        fly.remove();
        const badge = document.querySelector('.header-badge');
        if (badge) {
            badge.classList.add('animate-scale-in-bounce');
            setTimeout(() => badge.classList.remove('animate-scale-in-bounce'), 600);
        }
    };
};
