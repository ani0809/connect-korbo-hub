const btn = document.querySelector('[data-scroll-top], #scroll-top');
if (btn instanceof HTMLElement) {
    const onScroll = () => {
        if (window.scrollY > 320) {
            btn.classList.add('visible');
            btn.classList.remove('hidden');
        } else {
            btn.classList.remove('visible');
            btn.classList.add('hidden');
        }
    };

    onScroll();
    window.addEventListener('scroll', () => window.requestAnimationFrame(onScroll), { passive: true });

    btn.addEventListener('click', (e) => {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}
