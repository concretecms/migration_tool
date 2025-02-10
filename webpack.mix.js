const mix = require('laravel-mix');

mix.webpackConfig({
    externals: {
        jquery: 'jQuery'
    }
});

// Set our public path
mix.setPublicPath('assets');

// Build themes
mix
    .sass('resources/scss/backend/backend.scss', 'css/backend.css')
    .js('resources/js/backend.js', 'js/backend.js')
;

// Turn off notifications
mix
    .disableNotifications()
    .options({
        clearConsole: false,
    })
;
