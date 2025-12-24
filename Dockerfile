# ---------- 1) Build Vite assets ----------
FROM public.ecr.aws/docker/library/node:20-bookworm-slim AS vitebuild
WORKDIR /app

# install node deps
COPY package*.json ./
RUN npm ci || npm install

# copy full project and build
COPY . .
RUN npm run build

# ensure manifest exists (fail build if not)
RUN test -f /app/public/build/manifest.json


# ---------- 2) PHP runtime ----------
FROM public.ecr.aws/docker/library/php:8.2-fpm

# Install system deps + nginx
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx \
    libpng-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    zip unzip git curl \
  && docker-php-ext-install pdo pdo_mysql mbstring bcmath gd zip \
  && pecl install redis \
  && docker-php-ext-enable redis \
  && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=public.ecr.aws/docker/library/composer:2.2 /usr/bin/composer /usr/bin/composer

# App directory
WORKDIR /var/www/html

# Copy Laravel app
COPY . .

# Install PHP deps
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# ✅ Copy Vite build output (includes manifest.json)
COPY --from=vitebuild /app/public/build /var/www/html/public/build

# Laravel permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 777 storage bootstrap/cache

# NGINX config
COPY docker/nginx/default.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default \
 && mkdir -p /run/nginx

# Entrypoint
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/entrypoint.sh"]
