FROM php:8.3-apache

# Install PHP extensions
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libicu-dev libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql zip intl opcache gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite headers env

# Configure Apache
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && echo '<Directory /var/www/html/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
    CGIPassAuth On\n\
    FallbackResource /index.php\n\
</Directory>' > /etc/apache2/conf-available/symfony.conf \
    && a2enconf symfony

# Configure PHP
RUN echo "memory_limit=256M\n\
upload_max_filesize=20M\n\
post_max_size=25M\n\
opcache.enable=1\n\
opcache.memory_consumption=128\n\
opcache.max_accelerated_files=20000\n\
realpath_cache_size=4096K\n\
realpath_cache_ttl=600" > /usr/local/etc/php/conf.d/app.ini

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files and install dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy application
COPY . .

# Fix permissions BEFORE running scripts
# On s'assure que www-data est propriétaire avant de générer quoi que ce soit
RUN mkdir -p var/cache var/log var/sessions public/media \
    && chown -R www-data:www-data /var/www/html

# Switch to the web user to run final installation steps
# Cela garantit que le cache généré appartient au bon utilisateur
USER www-data

RUN composer dump-autoload --optimize --no-dev \
    && php bin/console cache:clear --env=prod \
    && php bin/console assets:install public \
    && php bin/console importmap:install \
    && php bin/console asset-map:compile

# Switch back to root to start Apache
USER root

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=3s CMD curl -f http://localhost/api/docs || exit 1

ENTRYPOINT ["docker-entrypoint.sh"]
