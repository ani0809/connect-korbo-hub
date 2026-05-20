const THEME_KEY = 'shop-theme';

function getStoredTheme() {
    try {
        return localStorage.getItem(THEME_KEY);
    } catch {
        return null;
    }
}

function setStoredTheme(mode) {
    try {
        localStorage.setItem(THEME_KEY, mode);
    } catch {
        /* ignore */
    }
}

function applyTheme(mode) {
    const html = document.documentElement;
    const next = mode === 'dark' ? 'dark' : 'light';
    html.setAttribute('data-theme', next);
    html.classList.toggle('dark', next === 'dark');
    setStoredTheme(next);
}

function initThemeToggle() {
    const stored = getStoredTheme();
    if (stored === 'dark' || stored === 'light') {
        applyTheme(stored);
    }

    document.querySelectorAll('[data-theme-toggle]').forEach((el) => {
        el.addEventListener('click', () => {
            const current =
                document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
            applyTheme(current === 'dark' ? 'light' : 'dark');
        });
    });
}

function initSmoothAnchors() {
    document.addEventListener('click', (e) => {
        const a = e.target.closest?.('a[href^="#"]');
        if (!(a instanceof HTMLAnchorElement)) return;
        const id = a.getAttribute('href');
        if (!id || id === '#') return;
        const target = document.querySelector(id);
        if (!target) return;
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
}

function initPreloaderFade() {
    window.addEventListener('load', () => {
        document.querySelectorAll('.preloader-overlay, #preloader').forEach((el) => {
            el.classList.add('fade-out', 'hide');
            setTimeout(() => el.remove(), 400);
        });
    });
}

/** Close mobile drawer / overlay patterns without Alpine */
function initMobileOverlays() {
    document.querySelectorAll('[data-close-drawer]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-close-drawer');
            const panel = id ? document.getElementById(id) : btn.closest('.mobile-menu-drawer, .drawer');
            panel?.classList.remove('open', 'mobile-open');
            document.querySelector('.mobile-menu-overlay')?.classList.remove('active');
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initThemeToggle();
    initSmoothAnchors();
    initPreloaderFade();
    initMobileOverlays();
});
