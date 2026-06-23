FROM php:8.3-cli

# System packages
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libonig-dev libxml2-dev libzip-dev \
    python3 python3-pip \
    default-mysql-client \
    supervisor \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && ln -sf /usr/bin/python3 /usr/bin/python \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

# PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Frontend assets
RUN npm install && npm run build

# Python dependencies
RUN pip3 install --break-system-packages --no-cache-dir \
    numpy scikit-learn joblib matplotlib mysql-connector-python

RUN chmod -R 775 storage bootstrap/cache

# supervisord
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 10000

ENV PORT=10000

CMD php artisan migrate --force \
    && php artisan config:cache \
    && php artisan route:cache \
    && exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
