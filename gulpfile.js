var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Less
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function (mix) {
    mix.sass('app.scss')
        .copy(
        'vendor/bower/bootstrap/dist/css/bootstrap.min.css',
        'public/css/vendor/bootstrap.css')
        .copy(
        'vendor/bower/font-awesome/css/font-awesome.min.css',
        'public/css/vendor/font-awesome.css')
        .copy(
        'vendor/bower/morrisjs/morris.css',
        'public/css/vendor/morris.css')
        .copy(
        'vendor/bower/font-awesome/fonts',
        'public/css/fonts')
        .copy(
        'vendor/bower/jquery/dist/jquery.min.js',
        'public/js/vendor/jquery.js')
        .copy(
        'vendor/bower/bootstrap/dist/js/bootstrap.min.js',
        'public/js/vendor/bootstrap.js')
        .copy(
        'vendor/bower/html5shiv/dist/html5shiv.min.js',
        'public/js/vendor/html5shiv.js')
        .copy(
        'vendor/bower/responsejs/response.min.js',
        'public/js/vendor/response.js')
        .copy(
        'vendor/bower/morrisjs/morris.min.js',
        'public/js/vendor/morris.js')
        .copy(
        'vendor/bower/raphael/raphael-min.js',
        'public/js/vendor/raphael.js')
        .copy(
        'vendor/bower/Flot/jquery.flot.js',
        'public/js/vendor/jquery.flot.js')
        .copy(
        'vendor/bower/Flot/jquery.flot.time.js',
        'public/js/vendor/jquery.flot.time.js')
        .copy(
        'vendor/bower/Flot/jquery.flot.selection.js',
        'public/js/vendor/jquery.flot.selection.js')
        .copy(
        'vendor/bower/Flot/excanvas.min.js',
        'public/js/vendor/excanvas.min.js');
});
