/**
 * Applies storefront theme tokens from window.themeSettings (set in layout)
 * onto document.documentElement. Theme Settings / JS can override any token.
 */
function applyThemeFromSettings(settings) {
    if (!settings || typeof settings !== 'object') {
        return;
    }
    const root = document.documentElement;

    const set = (key, cssVar) => {
        const v = settings[key];
        if (v === undefined || v === null || v === '') {
            return;
        }
        root.style.setProperty(cssVar, String(v));
    };

    set('primary', '--color-primary');
    set('primaryHover', '--color-primary-hover');
    set('primaryLight', '--color-primary-light');
    set('primaryDark', '--color-primary-dark');
    set('secondary', '--color-secondary');
    set('accent', '--color-accent');
    set('accentHover', '--color-accent-hover');

    if (settings.bodyFont) {
        root.style.setProperty(
            '--font-body',
            `'${String(settings.bodyFont).replace(/'/g, '')}', system-ui, sans-serif`,
        );
    }
    if (settings.headingFont) {
        root.style.setProperty(
            '--font-heading',
            `'${String(settings.headingFont).replace(/'/g, '')}', system-ui, sans-serif`,
        );
    }

    set('containerWidth', '--container-width');
    set('headerHeight', '--header-height');

    if (settings.borderRadius) {
        const r = String(settings.borderRadius);
        root.style.setProperty('--radius-sm', r);
        root.style.setProperty('--radius-md', `calc(${r} + 2px)`);
        root.style.setProperty('--radius-lg', `calc(${r} + 4px)`);
        root.style.setProperty('--radius-xl', `calc(${r} + 8px)`);
    }

    if (settings.background) {
        root.style.setProperty('--surface-page', String(settings.background));
    }
    if (settings.cardBackground) {
        root.style.setProperty('--surface-elevated', String(settings.cardBackground));
    }
}

document.addEventListener('DOMContentLoaded', () => {
    applyThemeFromSettings(window.themeSettings);
});

window.applyThemeFromSettings = applyThemeFromSettings;
