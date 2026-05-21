const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
    .vue({ version: 2 })
    .sass('resources/sass/app.scss', 'public/css', {
        sassOptions: {
            silenceDeprecations: ['legacy-js-api'],
        },
    });

if (mix.inProduction()) {
    mix.version();
}
