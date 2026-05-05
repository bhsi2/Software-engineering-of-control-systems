FROM php:8.3-fpm

# Установка системных зависимостей и расширений PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath gd

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Установка рабочей директории
WORKDIR /var/www/html

# Копирование файлов проекта
COPY . .

# Установка прав на директории хранения
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Установка зависимостей Composer (без dev-зависимостей в продакшн)
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Кэширование конфигурации и маршрутов 
RUN php artisan config:cache && php artisan route:cache

EXPOSE 9000
CMD ["php-fpm"]