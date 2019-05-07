set -e

echo 'Creating prestashop archive'

echo 'Installing composer packages ...'
if [ ! -d "vendor" ]; then
  composer install --no-scripts --no-autoloader
fi

echo 'Installing npm packages ...'
if [ ! -d "node_modules" ]; then
  npm install
fi

echo 'Creating boxtalconnect folder ...'
rm -rf boxtalconnect
mkdir boxtalconnect

echo 'Compiling less and minifying js files ...'
./node_modules/gulp/bin/gulp.js css
./node_modules/gulp/bin/gulp.js js

echo 'Copying module files into boxtalconnect folder ...'
cp -R src/* boxtalconnect/
cp -R vendor/boxtal/boxtal-php-poc/src boxtalconnect/lib

echo 'Removing less and non minified js files ...'
rm -rf src/views/css
rm src/views/js/*.min.js
rm -rf boxtalconnect/views/less
ls -d boxtalconnect/views/js/* | grep -v ".*.min.js" | grep -v ".*.php" | xargs rm

echo 'Creating archive ...'
rm -rf boxtalconnect.zip
zip -r boxtalconnect.zip boxtalconnect

echo 'Cleaning ...'
rm -rf boxtalconnect
