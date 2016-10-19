'use strict';
const gulp = require('gulp'),
    sass = require('gulp-sass'),
    browserify = require('gulp-browserify'),
    autoprefixer = require('gulp-autoprefixer'),
    browserSync = require('browser-sync').create(),
    cleanCSS = require('gulp-clean-css'),
    uglify = require('gulp-uglify'),
    reload = browserSync.reload;

//----- Config -----//
const build = '../public';

// SASS
gulp.task('sass', function () {
    gulp.src( 'scss/app.scss')
        .pipe(sass())
        .pipe(autoprefixer({
            browsers: ['> 1%', 'last 40 versions'],
            cascade: true
        }))
        .pipe(cleanCSS())
        .pipe(gulp.dest( build + '/css/' ))
        .pipe(browserSync.stream());
});

// JS
gulp.task('js', function () {
    return gulp.src('js/*.js')
    .pipe(browserify())
    // .pipe(uglify())
    .pipe(gulp.dest( build + '/js/' ));
});

// JSON
gulp.task('json', function () {
    return gulp.src('js/*.json')
    .pipe(gulp.dest( build + '/js/' ));
});

// Browsersync
gulp.task('serve', ['sass'], function() {
    browserSync.init({
        proxy: 'bogs.dev:8888'
    });
    gulp.watch('./scss/**.scss', ['sass']);
    gulp.watch('./js/**/**.js', ['js']);
    gulp.watch('../../app/Resources/views/*.twig').on('change', reload);
});

gulp.task('default', ['sass', 'js', 'json', 'serve']); //   <------ Default -----//
