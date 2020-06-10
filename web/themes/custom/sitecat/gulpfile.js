var gulp = require('gulp');
var sass = require('gulp-sass');
var minifyCss = require("gulp-minify-css");
var sassGlob = require('gulp-sass-glob');

// Compile sass into CSS & auto-inject into browsers
gulp.task('sass', function () {
  return gulp.src([
    'node_modules/bootstrap/scss/bootstrap.scss',
    'scss/style.scss'
  ])
    .pipe(sassGlob())
    .pipe(sass().on('error', sass.logError))
    .pipe(gulp.dest("css"))
    .pipe(sass({outputStyle: 'compressed'}))
    .pipe(minifyCss());
});

// Move the javascript files into our js folder
gulp.task('js', function () {
  return gulp.src([
    'node_modules/bootstrap/dist/js/bootstrap.min.js',
    'node_modules/jquery/dist/jquery.min.js',
    'node_modules/popper.js/dist/umd/popper.min.js'
  ])
    .pipe(gulp.dest("js"));
});

// Static Server + watching scss/html files
gulp.task('serve', ['sass'], function () {
  gulp.watch([
    'node_modules/bootstrap/scss/bootstrap.scss',
    'scss/*.scss'
  ], ['sass']);
});

gulp.task('default', ['js', 'serve']);
