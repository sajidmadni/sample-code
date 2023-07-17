var Encore = require('@symfony/webpack-encore');

Encore
    // directory where compiled assets will be stored
    // web/dev_build/ or web/prod_build/
    .setOutputPath('web/prod_build/')
    // public path used by the web server to access the output path
    // /dev_build or /prod_build
    .setPublicPath('/prod_build')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('app', './assets/js/app.js')
    .addEntry('messageStyle', './assets/css/components/SecureText/messageStyle.css')
    .addEntry('chatvia_saas', './assets/css/components/SecureText/scss/themes.scss')
    .addEntry('taskStyle', './assets/css/components/Task/taskStyle.css')
    .configureBabel(() => {}, {
        useBuiltIns: 'usage',
        corejs: 3
    })

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())
    .enableReactPreset()


    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment if you use Sass/SCSS files
    .enableSassLoader()

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();