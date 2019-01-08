var gulp = require('gulp'),
  less = require('gulp-less'),
  plumber = require('gulp-plumber'),
  terser = require('gulp-terser'),
  rename = require('gulp-rename'),
  notify = require('gulp-notify'),
  cleanCSS = require('gulp-clean-css'),
  autoprefixer = require('gulp-autoprefixer'),
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
        return gulp.src([assetsDir + '/js/*.js', '!' + assetsDir + '/js/*.min.js'])
        .pipe(plumber({errorHandler: onError}))
        .pipe(terser({
          ie8: true,
          output: {
            comments: /.*license.*/
          }
        }))
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest(assetsDir + '/js'));
    }
);

// Styles task for local wordpress
gulp.task(
    'css', function () {
        return gulp.src([assetsDir + '/less/*.less'])
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
        .pipe(cleanCSS({compatibility: 'ie8'}))
        .pipe(gulp.dest(assetsDir + '/css'));
    }
);
