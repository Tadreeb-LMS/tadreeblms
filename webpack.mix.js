const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.setPublicPath('public');
mix.options({
    processCssUrls: false
});

mix.sass('resources/sass/frontend/app.scss', 'css/frontend.css')
    .sass('resources/sass/frontend-rtl/app.scss', 'css/frontend-rtl.css')
    .sass('resources/sass/backend/app.scss', 'css/backend.css')
    .js('resources/js/frontend/app.js', 'js/frontend.js')
    .js([
        'resources/js/backend/before.js',
        'resources/js/backend/app.js',
        'resources/js/backend/after.js'
    ], 'js/backend.js')
    .extract([
        'jquery',
        'jquery-ui-dist/jquery-ui',
        'bootstrap',
        'popper.js/dist/umd/popper',
        'axios',
        'sweetalert2',
    ]);

mix.override(config => {
    config.plugins = (config.plugins || []).filter(
        plugin => plugin.constructor.name !== 'WebpackBarPlugin'
    );
});

if (mix.inProduction() || process.env.npm_lifecycle_event !== 'hot') {
    mix.version();
}
