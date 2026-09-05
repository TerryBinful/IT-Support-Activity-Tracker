# Single-stage build: fewer base images, more reliable on Docker Desktop for Windows.
FROM php:8.4-apache-bookworm

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y --no-install-recommends \
    ca-certificates curl gnupg unzip git \
    libicu-dev libzip-dev libpq-dev libonig-dev libxml2-dev \
    libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" bcmath gd intl mbstring opcache pdo_pgsql pgsql zip \
    && a2enmod rewrite headers expires \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Serve Laravel's public directory directly from Apache.
RUN sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && printf '%s\n' \
    '<Directory /var/www/html/public>' \
    '    AllowOverride All' \
    '    Require all granted' \
    '</Directory>' > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

# Bootstrap Laravel inside the image, then overlay project-owned files.
RUN composer create-project laravel/laravel:^13.0 /tmp/laravel --no-interaction --prefer-dist --no-security-blocking

COPY composer.json /tmp/laravel/composer.json
WORKDIR /tmp/laravel
RUN composer update --no-interaction --prefer-dist --optimize-autoloader --no-security-blocking

COPY app/ /tmp/laravel/app/
COPY database/migrations/ /tmp/custom-migrations/
COPY database/seeders/ /tmp/custom-seeders/
COPY resources/views/ /tmp/laravel/resources/views/
COPY resources/css/ /tmp/laravel/resources/css/
COPY resources/js/ /tmp/laravel/resources/js/
COPY routes/ /tmp/laravel/routes/
COPY tests/ /tmp/laravel/tests/
COPY bootstrap/app.php /tmp/laravel/bootstrap/app.php
COPY .env.example /tmp/laravel/.env.example
COPY package.json /tmp/laravel/package.json
COPY vite.config.js /tmp/laravel/vite.config.js

RUN cp /tmp/custom-migrations/*.php database/migrations/ 2>/dev/null || true \
    && cp /tmp/custom-seeders/*.php database/seeders/ 2>/dev/null || true \
    && npm install --no-audit --no-fund \
    && npm run build \
    && rm -rf /var/www/html/* \
    && cp -a /tmp/laravel/. /var/www/html/ \
    && rm -rf /tmp/laravel /tmp/custom-migrations /tmp/custom-seeders

WORKDIR /var/www/html

COPY scripts/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80
ENTRYPOINT ["/bin/sh", "/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
