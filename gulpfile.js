var gulp = require('gulp'),
  less = require('gulp-less'),
  plumber = require('gulp-plumber'),
  terser = require('gulp-terser'),
  rename = require('gulp-rename'),
  notify = require('gulp-notify'),
  cleanCSS = require('gulp-clean-css'),
  autoprefixer = require('gulp-autoprefixer'),
  mergeStream  = require( 'merge-stream' ),
  gap = require('gulp-append-prepend'),
  assetsDir = 'src/views';

/* Error Notification
 ================================================================================== */
var onError = function (err) {
    notify.onError(
        {
            title: "Oops, some error:",
            message: "<%= error.message %>"
        }
    )(err);
    this.emit('end');
};

// JS concat & minify task for local wordpress
gulp.task(
    'js', function () {
        return mergeStream(
          gulp.src(
            [
              assetsDir + '/js/*.js',
              '!' + assetsDir + '/js/*.min.js'
            ]
          )
          .pipe(plumber({errorHandler: onError}))
          .pipe(terser({
            ie8: true,
            output: {
              comments: /.*license.*/
            }
          }))
          .pipe(rename({suffix: '.min'})),
          gulp.src(
            [
              'node_modules/mapbox-gl/dist/mapbox-gl.js'
            ]
          )
          .pipe(terser({
             ie8: true,
             compress: false,
             output: {
               comments: /.*license.*/
             }
          }))
          .pipe(rename({ suffix: '.min' })),
          gulp.src(
            [
              'node_modules/promise-polyfill/dist/polyfill.min.js'
            ]
          )
          .pipe(gap.prependText('*/'))
          .pipe(gap.prependFile('node_modules/promise-polyfill/LICENSE'))
          .pipe(gap.prependText('/*'))
          .pipe(rename("promise-polyfill.min.js"))
        )
        .pipe(gulp.dest(assetsDir + '/js'));
    }
);

// Styles task for local wordpress
gulp.task(
    'css', function () {
        return mergeStream(
          gulp.src([assetsDir + '/less/*.less'])
          .pipe(plumber({errorHandler: onError}))
          .pipe(less())
          .pipe(
              autoprefixer(
                  {
                      browsers: ['last 2 versions', '>1%', 'ie 9'],
                      cascade: false
                  }
              )
          )
          .pipe(cleanCSS({compatibility: 'ie8'})),
          gulp.src(
            [
              'node_modules/mapbox-gl/dist/mapbox-gl.css'
            ]
          )
        )
        .pipe(gulp.dest(assetsDir + '/css'));
    }
);
