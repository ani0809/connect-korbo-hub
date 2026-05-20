const toggle = document.getElementById('mobile-menu-toggle');
const drawer = document.getElementById('mobile-menu-drawer');
const overlay = document.getElementById('mobile-menu-overlay');

function closeMenu() {
    drawer?.classList.remove('open');
    overlay?.classList.remove('open');
}

function openMenu() {
    drawer?.classList.add('open');
    overlay?.classList.add('open');
}

toggle?.addEventListener('click', () => {
    if (drawer?.classList.contains('open')) {
        closeMenu();
    } else {
        openMenu();
    }
});

overlay?.addEventListener('click', closeMenu);

let touchStart = 0;
drawer?.addEventListener('touchstart', (e) => {
    touchStart = e.changedTouches[0].clientX;
});
drawer?.addEventListener('touchend', (e) => {
    const delta = e.changedTouches[0].clientX - touchStart;
    if (delta < -60) {
        closeMenu();
    }
});

document.querySelectorAll('.mobile-submenu-toggle').forEach((btn) => {
    btn.addEventListener('click', () => {
        btn.parentElement?.classList.toggle('open');
    });
});
