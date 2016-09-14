const gulp = require('gulp'),
    sass = require('gulp-sass'),
    autoprefixer = require('gulp-autoprefixer'),
    browserSync = require('browser-sync').create(),
    reload = browserSync.reload;

//----- Config -----//
const build = '../public/css/';

// SASS
gulp.task('sass', function () {
    gulp.src( 'scss/app.scss')
        .pipe(sass())
        .pipe(autoprefixer({
            browsers: ['> 1%', 'last 40 versions'],
            cascade: true
        }))
        .pipe(gulp.dest( build ))
        .pipe(browserSync.stream());
});

// Browsersync
gulp.task('browser-sync', ['sass'], function() {
	browserSync.init({
		server: {
			baseDir: './scss',
            port: 8080
		}
	});
});

gulp.task('serve', ['sass'], function() {
    browserSync.init({
        proxy: 'bogs.dev:8888'
    });

    gulp.watch('./scss/**.scss', ['sass']);
    gulp.watch('../../app/Resources/views/page.index.html.twig').on('change', reload);
});


gulp.task('default', ['sass', 'serve']);
