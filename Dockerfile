FROM buildpack-deps:stretch-scm
ARG PHP_VERSION
ARG PS_VERSION

RUN curl -sL https://deb.nodesource.com/setup_8.x | bash - \
 && apt-get update && apt-get install -y --no-install-recommends \
    apache2 \
    apt-transport-https \
    lsb-release \
    ca-certificates \
    sudo \
    nodejs \
    xvfb \
    xauth \
    unzip

RUN curl https://packages.sury.org/php/apt.gpg | apt-key add - \
 && echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list \
 && apt-get update && apt-get install -y --no-install-recommends \
    php${PHP_VERSION} \
    php${PHP_VERSION}-mysql \
    php${PHP_VERSION}-mbstring \
    php${PHP_VERSION}-soap \
    php${PHP_VERSION}-curl \
    php${PHP_VERSION}-dom \
    php${PHP_VERSION}-intl \
    php${PHP_VERSION}-gd \
    php${PHP_VERSION}-zip

RUN wget -q -O - https://dl-ssl.google.com/linux/linux_signing_key.pub | sudo apt-key add -
RUN sh -c 'echo "deb https://dl.google.com/linux/chrome/deb/ stable main" >> /etc/apt/sources.list.d/google.list'
RUN apt-get update && apt-get install -y --no-install-recommends \
    google-chrome-stable

RUN sed -i "172,\$s/AllowOverride None/AllowOverride All/g" /etc/apache2/apache2.conf

RUN echo "mysql-server-5.6 mysql-server/root_password password password" | sudo debconf-set-selections \
 && echo "mysql-server-5.6 mysql-server/root_password_again password password" | sudo debconf-set-selections \
 && sudo apt-get install -y mysql-server
RUN /etc/init.d/mysql start \
 && sudo mysql -u root -ppassword -e "CREATE USER 'dbadmin'@'localhost' IDENTIFIED BY 'dbpass';" \
 && sudo mysql -u root -ppassword -e "GRANT ALL PRIVILEGES ON *.* TO 'dbadmin'@'localhost' WITH GRANT OPTION;" \
 && sudo mysql -u root -ppassword -e "FLUSH PRIVILEGES;"

RUN useradd -m docker && echo "docker:docker" | chpasswd && adduser docker sudo \
 && echo "docker ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers

RUN curl -sS https://getcomposer.org/installer | \
 php -- --install-dir=/usr/bin/ --filename=composer

ENV HOME=/home/docker
WORKDIR $HOME
COPY composer.json $HOME
RUN composer install --no-scripts --no-autoloader --no-dev --prefer-source
RUN chown -R docker:docker $HOME
COPY build/entrypoint.sh $HOME/build/
ENTRYPOINT $HOME/build/entrypoint.sh
RUN composer dump-autoload --optimize

COPY build/install-ps.sh $HOME/build/
RUN /etc/init.d/apache2 start \
 && /etc/init.d/mysql start \
 && /bin/bash ./build/install-ps.sh $PS_VERSION

COPY package.json $HOME
COPY package-lock.json $HOME
COPY gulpfile.js $HOME
RUN npm install
RUN npm install -g gulp
RUN npm install -g gulp-cli

COPY . $HOME
RUN chown -R docker:docker $HOME

RUN gulp css
RUN gulp js

RUN mkdir $HOME/src/lib \
 && cp -R $HOME/vendor/boxtal/boxtal-php-poc/src/* $HOME/src/lib

RUN mkdir -p /var/www/html/modules/boxtal \
 && cp -R $HOME/src/* /var/www/html/modules/boxtal \
 && chown -R www-data:www-data /var/www/html \
 && find /var/www/html -type d -exec chmod 775 {} \; \
 && find /var/www/html -type f -exec chmod 644 {} \;

USER docker
