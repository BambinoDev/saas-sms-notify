FROM php:8.2-fpm-alpine

# Installer les dépendances système
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    libpng-dev \
    oniguruma-dev \
    linux-headers \
    $PHPIZE_DEPS

# Installer les extensions PHP
RUN docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    gd \
    zip \
    mbstring \
    opcache

# Installer Redis via PECL
RUN pecl install redis \
    && docker-php-ext-enable redis

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copier la configuration PHP personnalisée
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Définir le répertoire de travail
WORKDIR /var/www/html

# Configurer les permissions
RUN chown -R www-data:www-data /var/www/html

# Changer vers l'utilisateur www-data
USER www-data

# Exposer le port PHP-FPM
EXPOSE 9000

# Commande par défaut
CMD ["php-fpm"]
