var gulp = require('gulp');
var gulpif = require('gulp-if');
var sass = require('gulp-sass');
var concat = require('gulp-concat');
var postcss = require('gulp-postcss');
const rename = require('gulp-rename');
const uglify = require('gulp-terser');
const cleanCSS = require('gulp-clean-css');//To Minify CSS files
const del = require('del');
const logSymbols = require('log-symbols'); //For Symbolic Console logs :) :P 
const { argv } = require('yargs');

const isProduction = (argv.production === undefined) ? false : true;

function styles() {
    var tailwindcss = require('tailwindcss');

    return gulp.src('styles/styles.scss')
        .pipe(sass().on('error', sass.logError))
        .pipe(postcss([
            tailwindcss('./tailwind.config.js'),
            require('autoprefixer'),
        ]))
        .pipe(gulpif(isProduction, cleanCSS({compatibility: 'ie8'})))
        .pipe(rename("output.css"))
        .pipe(gulp.dest('./public/dist'));
}

function scripts() {
    return gulp.src('scripts/**/*.js')
        .pipe(concat("output.js"))
        .pipe(gulpif(isProduction, uglify()))
        .pipe(gulp.dest('./public/dist'));

}

function clean() {
    return del("./public/dist");
}

function buildFinish(done) {
    console.log("\n\t" + logSymbols.info,`Build is complete.\n`);
    done();
}

exports.build = gulp.series(
    clean,
    gulp.parallel(styles, scripts),
    buildFinish
)

exports.default = function() {
    gulp.watch('styles/**/*.scss', gulp.series(
        clean,
        gulp.parallel(styles, scripts),
        buildFinish
    ));
}