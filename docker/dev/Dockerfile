FROM composer:2.8.5 AS composer
FROM php:8.3.16-apache-bookworm

## Предустановленные константы
ARG DEBIAN_FRONTEND=noninteractive
ARG TZ='Europe/Kaliningrad'
ARG USER_ID=1000
ARG GROUP_ID=1000

## Берем готовый composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

## Чтобы было проще добавлять расширения используем пакет docker-php-extension-installer
RUN curl -sSLf -o /usr/local/bin/install-php-extensions https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions && \
    chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions apcu opcache intl mysqli pdo_mysql zip amqp sockets xdebug && \
    a2enmod rewrite && \
    apt-get update && \
    apt-get install -y sudo vim git && \
    rm -rf /var/cache/apk/* && \
## Трюк, чтобы www-data имела uid/git, как у пользователя и не нужно было постоянно делать chown
    usermod -u ${USER_ID} www-data && groupmod -g ${GROUP_ID} www-data && \
    mkdir -p /var/www/html && \
    chown -R www-data:www-data /var/www/html

COPY --chown=root:root xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
COPY --chown=root:root 000-default.conf /etc/apache2/sites-available/000-default.conf
COPY --chown=root:root environment.conf /etc/apache2/conf-enabled/environment.conf
COPY --chown=root:root php.ini-development /usr/local/etc/php/php.ini

## Переходим/объявляем в рабочий каталог
WORKDIR /var/www/html

## Поставим пользователя по умолчанию, чтобы при действиях из PHPStorm устанавливались правильные права
USER www-data

EXPOSE "80/tcp"

CMD ["apache2ctl", "-D", "FOREGROUND"]
