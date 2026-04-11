# ==========================================
# STEP 1: Build Frontend (VueJS / Vite)
# ==========================================
FROM node:18-alpine AS fe
WORKDIR /app
COPY package.json package-lock.json* ./
# Dùng npm ci giúp build nhanh hơn và đảm bảo chính xác phiên bản packages
RUN npm install
COPY . .
RUN npm run prod

# ==========================================
# STEP 2: Build Backend & Production Server (PHP + Apache)
# ==========================================
FROM php:8.2-apache AS be

# Sử dụng file cấu hình php.ini tối ưu sẵn cho môi trường production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Cài đặt các thư viện hệ thống (thêm git, unzip để chạy Composer) và PHP Extensions cần thiết cho Laravel
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libpq-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    # Thêm exif vào đây để fix lỗi thiếu ext-exif cho thư viện spatie/image
    && docker-php-ext-install gd pdo pdo_mysql pdo_pgsql zip bcmath pcntl opcache exif \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Bật mod_rewrite của Apache
RUN a2enmod rewrite

# Đổi thư mục gốc của Apache trỏ thẳng vào /public của Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

# Copy toàn bộ mã nguồn vào
COPY . /var/www/html

# Copy Composer từ image chính thức vào dùng trực tiếp
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer
# Thêm --ignore-platform-req=ext-http để bỏ qua bắt buộc cài ext-http (rất nặng và thường dư thừa với Guzzle)
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-req=ext-http \
    && composer dump-autoload --optimize

# Copy mã nguồn frontend từ STEP 1 (fe) chuyển sang
COPY --from=fe /app/public /var/www/html/public

# Phân quyền cho Storage và Bootstrap Cache để Laravel có thể ghi file
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Mở port 80
EXPOSE 80