/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            screens: {
                '2xl': '1400px',
            },
            colors: {
                // Admin shell (matches Lovable Zenith / shadcn sidebar variables)
                sidebar: {
                    DEFAULT: 'hsl(var(--sidebar-background) / <alpha-value>)',
                    foreground: 'hsl(var(--sidebar-foreground) / <alpha-value>)',
                    primary: 'hsl(var(--sidebar-primary) / <alpha-value>)',
                    'primary-foreground':
                        'hsl(var(--sidebar-primary-foreground) / <alpha-value>)',
                    accent: 'hsl(var(--sidebar-accent) / <alpha-value>)',
                    'accent-foreground':
                        'hsl(var(--sidebar-accent-foreground) / <alpha-value>)',
                    border: 'hsl(var(--sidebar-border) / <alpha-value>)',
                },
                primary: 'hsl(var(--primary) / <alpha-value>)',
                secondary: 'hsl(var(--secondary) / <alpha-value>)',
                accent: 'hsl(var(--accent) / <alpha-value>)',
                background: 'hsl(var(--background) / <alpha-value>)',
                foreground: 'hsl(var(--foreground) / <alpha-value>)',
                card: 'hsl(var(--card) / <alpha-value>)',
                border: 'hsl(var(--border) / <alpha-value>)',
                muted: 'hsl(var(--muted) / <alpha-value>)',
                success: 'hsl(var(--success) / <alpha-value>)',
                warning: 'hsl(var(--warning) / <alpha-value>)',
                destructive: 'hsl(var(--destructive) / <alpha-value>)',
                info: 'hsl(var(--info) / <alpha-value>)',
            },
            boxShadow: {
                card: 'var(--shadow-card)',
                elevated: 'var(--shadow-elevated)',
                glow: 'var(--shadow-glow)',
                'glow-accent': 'var(--shadow-glow-accent)',
            },
            backgroundImage: {
                'gradient-primary': 'var(--gradient-primary)',
                'gradient-accent': 'var(--gradient-accent)',
            },
            fontFamily: {
                body: ['var(--font-body)', 'sans-serif'],
                heading: ['var(--font-heading)', 'sans-serif'],
            },
            spacing: {
                18: '4.5rem',
                88: '22rem',
                112: '28rem',
            },
            borderRadius: {
                shop: 'var(--border-radius)',
                md: 'var(--radius-md)',
                lg: 'var(--radius-lg)',
                xl: 'var(--radius-xl)',
            },
            maxWidth: {
                container: 'var(--container-width)',
            },
            keyframes: {
                'fade-in': {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                'fade-in-up': {
                    '0%': { opacity: '0', transform: 'translateY(8px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                'scale-in': {
                    '0%': { opacity: '0', transform: 'scale(0.96)' },
                    '100%': { opacity: '1', transform: 'scale(1)' },
                },
                shake: {
                    '0%,100%': { transform: 'translateX(0)' },
                    '20%,60%': { transform: 'translateX(-2px)' },
                    '40%,80%': { transform: 'translateX(2px)' },
                },
                'pulse-glow': {
                    '0%,100%': { boxShadow: 'var(--shadow-glow)' },
                    '50%': { boxShadow: 'none' },
                },
            },
            animation: {
                'fade-in': 'fade-in 0.4s var(--ease-smooth)',
                'fade-in-up': 'fade-in-up 0.6s var(--ease-spring)',
                'scale-in': 'scale-in 0.3s var(--ease-spring)',
                shake: 'shake 0.4s var(--ease-smooth)',
                'pulse-glow': 'pulse-glow 2s var(--ease-smooth) infinite',
            },
        },
    },
    /* Ensure Zenith sidebar utilities exist even if JIT misses Blade/@class branches */
    safelist: [
        { pattern: /^text-sidebar/ },
        { pattern: /^bg-sidebar/ },
        { pattern: /^border-sidebar/ },
        { pattern: /^shadow-(lg|xs|elevated|glow)/ },
        'sidebar-active-glow',
    ],
    plugins: [],
};
