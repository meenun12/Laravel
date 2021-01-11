FROM php:7.4-fpm

# Copy composer.lock and composer.json
COPY composer.lock composer.json /var/www/dealroom-count-bigquery/

# Set working directory
WORKDIR /var/www/dealroom-count-bigquery/

# Install dependencies
RUN apt-get update && apt-get upgrade -y && apt-get install -y \
      procps \
      nano \
      git \
      unzip \
      libicu-dev \
      zlib1g-dev \
      libxml2 \
      libxml2-dev \
      libreadline-dev \
      supervisor \
      cron \
      libzip-dev \
    && docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
      pdo_mysql \
      sockets \
      intl \
      opcache \
      zip \
    && rm -rf /tmp/* \
    && rm -rf /var/list/apt/* \
    && rm -rf /var/lib/apt/lists/* \
    && apt-get clean

# Clear cache
#RUN apt-get clean && rm -rf /var/lib/apt/lists/* (note: removed because alreaddy added above)

# Install extensions
# RUN app.sh-php-ext-install pdo_mysql mbstring zip exif pcntl
#RUN app.sh-php-ext-configure gd --with-gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ --with-png-dir=/usr/include/
#RUN app.sh-php-ext-install gd (note: removed because throwing error https://www.digitalocean.com/community/tutorials/how-to-set-up-laravel-nginx-and-mysql-with-docker-compose)
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Add user for laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# XDebug log init
RUN touch /var/log/xdebug.log \
    && chown www:www /var/log/xdebug.log

# Copy existing application directory contents
COPY . /var/www/dealroom-count-bigquery/

# Copy existing application directory permissions
COPY --chown=www:www . /var/www/dealroom-count-bigquery

# Change current user to www
USER www

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
