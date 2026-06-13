FROM node:18-alpine AS fe
WORKDIR /app
COPY package.json package-lock.json* ./
# Dùng npm install thay vì npm ci để tránh lỗi nếu package-lock.json không đồng bộ với package.json
RUN npm install
COPY . .
RUN npm run prod

# CHUYỂN TỪ DEBIAN SANG ALPINE ĐỂ TỐI ƯU DUNG LƯỢNG (Giảm từ ~400MB xuống còn ~80-100MB)
FROM php:8.2-fpm-alpine AS be

# Sử dụng file cấu hình php.ini tối ưu sẵn cho môi trường production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Cài đặt Nginx, Supervisor và các thư viện runtime (không kèm header code)
RUN apk add --no-cache \
    nginx \
    supervisor \
    freetype \
    libjpeg-turbo \
    libpng \
    libzip \
    postgresql-libs \
    zip \
    unzip \
    git \
    bash \
    # Gom các thư viện -dev vào nhóm .build-deps để cài tạm thời phục vụ việc compile
    && apk add --no-cache --virtual .build-deps \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libzip-dev \
    postgresql-dev \
    # Compile PHP Extensions
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql pdo_pgsql zip bcmath pcntl opcache exif \
    # QUAN TRỌNG: Xóa toàn bộ file -dev, source code rác sau khi compile xong để giảm dung lượng
    && apk del .build-deps

WORKDIR /var/www/html

# Copy toàn bộ mã nguồn vào
COPY . /var/www/html

# Copy Composer từ image chính thức vào dùng trực tiếp
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer
# Thêm --ignore-platform-req=ext-http để bỏ qua bắt buộc cài ext-http
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-req=ext-http \
    && composer dump-autoload --optimize

# Copy mã nguồn frontend từ STEP 1 (fe) chuyển sang
COPY --from=fe /app/public /var/www/html/public

# Phân quyền cho Storage và Bootstrap Cache để Laravel có thể ghi file
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# COPY cấu hình Nginx và Supervisor vào container
# Nginx trên Alpine dùng thư mục http.d thay vì sites-available
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
RUN mkdir -p /var/log/supervisor

# Mở port 80 cho Nginx thay vì 9000 cho PHP-FPM
EXPOSE 80

# Chạy Supervisor để quản lý cả 2 tiến trình (Nginx và PHP-FPM)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]