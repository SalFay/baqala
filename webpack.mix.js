const mix = require('laravel-mix')

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.sass('resources/sass/app.scss', 'public/css').options({
	 processCssUrls: false
 })
 .js('resources/js/app.js', 'public/js')
 .copy('node_modules/admin-lte/dist/css', 'public/css/admin-lte')
 .copy('node_modules/admin-lte/dist/js', 'public/js/admin-lte')
 .copy('node_modules/admin-lte/plugins', 'public/js/admin-lte/plugins')
 .copy('node_modules/admin-lte/dist/img', 'public/img/admin-lte')
 .postCss('resources/css/app.css', 'public/css', [
	 require('tailwindcss'),
 ])

if (mix.inProduction()) {
	mix.version()
}
