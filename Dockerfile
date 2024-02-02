FROM php:8.0-apache

ENV COMPOSER_ALLOW_SUPERUSER=1

EXPOSE 80
WORKDIR /app

# git, unzip & zip are for composer
RUN apt-get update -qq && \
    apt-get install -qy \
    git \
    gnupg \
    unzip \
    zlib1g-dev \
    libpng-dev  \
    libzip-dev \
    zip && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# PHP Extensions
RUN apt-get update && apt-get install -y libcurl4-openssl-dev pkg-config libssl-dev
RUN pecl install mongodb
RUN docker-php-ext-install -j$(nproc) opcache gd zip
RUN docker-php-ext-enable mongodb
COPY conf/php.ini /usr/local/etc/php/conf.d/app.ini

# Apache
COPY conf/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY conf/apache.conf /etc/apache2/conf-available/z-app.conf
COPY . /app

# update composer
RUN composer update

# Create and set permissions for the uploads directory
RUN mkdir /app/uploads && chmod 755 /app/uploads && chown www-data:www-data /app/uploads

RUN a2enmod rewrite remoteip && \
    a2enconf z-app
