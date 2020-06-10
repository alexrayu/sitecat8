var gulp = require('gulp');
var sass = require('gulp-sass');
var minifyCss = require("gulp-minify-css");
var sassGlob = require('gulp-sass-glob');

gulp.task('sass', function () {
  return gulp.src(['assets/scss/sitecat_admin.scss'])
    .pipe(sassGlob())
    .pipe(sass().on('error', sass.logError))
    .pipe(gulp.dest('assets/css'))
    .pipe(sass({outputStyle: 'compressed'}))
    .pipe(minifyCss());
});

gulp.task('serve', ['sass'], function () {
  gulp.watch([
    'assets/scss/*.scss'
  ], ['sass']);
});

gulp.task('default', ['serve']);
