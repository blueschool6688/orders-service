# ==========================================
# GIAI ĐOẠN 1: Build Frontend (VueJS / Vite)
# ==========================================
FROM node:18-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm install
COPY . .
RUN npm run build

# ==========================================
# GIAI ĐOẠN 2: Cài đặt thư viện Backend (Composer)
# ==========================================
FROM composer:2.6 AS backend
WORKDIR /app
COPY composer.json composer.lock* ./
# Cài đặt thư viện bỏ qua các script chưa cần thiết
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist
COPY . .
# Tạo file autoload tối ưu cho production
RUN composer dump-autoload --optimize

# ==========================================
# GIAI ĐOẠN 3: Đóng gói Server chạy thực tế (PHP + Apache)
# ==========================================
FROM php:8.2-apache

# 1. Cài đặt các thư viện hệ thống và PHP Extensions cần thiết cho Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libpq-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql pdo_pgsql zip bcmath pcntl

# 2. Bật mod_rewrite của Apache (Bắt buộc để URL của Laravel hoạt động)
RUN a2enmod rewrite

# 3. Đổi thư mục gốc của Apache trỏ thẳng vào thư mục /public của Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

# 4. Copy mã nguồn từ Giai đoạn 1 và 2 sang
COPY --from=backend /app /var/www/html
COPY --from=frontend /app/public /var/www/html/public

# 5. Phân quyền cho thư mục Storage và Bootstrap Cache để Laravel có thể ghi file
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 6. Mở port 80
EXPOSE 80