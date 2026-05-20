class StickyHeader {
    constructor() {
        this.mainRow =
            document.querySelector('.header-main.sticky-row') ||
            document.querySelector('.site-header .sticky-row') ||
            document.querySelector('.header-row.sticky-row');
        this.lastScrollY = 0;
        this.ticking = false;
        if (this.mainRow) {
            this.init();
        }
    }

    init() {
        window.addEventListener('scroll', () => {
            this.lastScrollY = window.scrollY;
            if (!this.ticking) {
                window.requestAnimationFrame(() => {
                    this.update();
                    this.ticking = false;
                });
                this.ticking = true;
            }
        });
    }

    update() {
        if (this.lastScrollY > 0) {
            this.mainRow.classList.add('is-sticky');
        } else {
            this.mainRow.classList.remove('is-sticky');
        }
    }
}

new StickyHeader();
