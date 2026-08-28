# Build the Laravel application completely inside Docker.
FROM composer:2 AS laravel-builder
WORKDIR /build

RUN composer create-project laravel/laravel:^13.0 laravel --no-interaction --prefer-dist

# Replace the generated dependency manifest with the project's declared dependencies.
COPY composer.json /build/laravel/composer.json
WORKDIR /build/laravel
RUN composer update --no-interaction --prefer-dist --optimize-autoloader

# Overlay application-owned source files from the repository.
COPY app/ /build/laravel/app/
COPY database/migrations/ /build/custom-migrations/
COPY database/seeders/ /build/custom-seeders/
COPY resources/views/ /build/laravel/resources/views/
COPY resources/css/ /build/laravel/resources/css/
COPY resources/js/ /build/laravel/resources/js/
COPY routes/ /build/laravel/routes/
COPY bootstrap/app.php /build/laravel/bootstrap/app.php
COPY .env.example /build/laravel/.env.example
COPY package.json /build/laravel/package.json
COPY vite.config.js /build/laravel/vite.config.js
RUN cp /build/custom-migrations/*.php database/migrations/ 2>/dev/null || true \
    && cp /build/custom-seeders/*.php database/seeders/ 2>/dev/null || true

FROM node:22-alpine AS assets-builder
WORKDIR /app
COPY --from=laravel-builder /build/laravel/package.json ./package.json
COPY --from=laravel-builder /build/laravel/vite.config.js ./vite.config.js
COPY --from=laravel-builder /build/laravel/resources ./resources
COPY --from=laravel-builder /build/laravel/public ./public
RUN npm install --no-audit --no-fund && npm run build

FROM php:8.4-apache-bookworm
WORKDIR /var/www/html

RUN apt-get update && apt-get install -y --no-install-recommends \
    libicu-dev libzip-dev libpq-dev libonig-dev libxml2-dev unzip git curl \
    && docker-php-ext-install -j"$(nproc)" bcmath intl mbstring opcache pdo_pgsql pgsql zip \
    && a2enmod rewrite headers expires \
    && rm -rf /var/lib/apt/lists/*

# Serve Laravel's public directory directly from Apache.
RUN sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && printf '%s\n' \
    '<Directory /var/www/html/public>' \
    '    AllowOverride All' \
    '    Require all granted' \
    '</Directory>' > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

COPY --from=laravel-builder /build/laravel /var/www/html
COPY --from=assets-builder /app/public/build /var/www/html/public/build
COPY scripts/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh \
    && mkdir -p /var/www/html/storage/framework/cache /var/www/html/storage/framework/sessions /var/www/html/storage/framework/views /var/www/html/storage/logs \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
