# Sử dụng bản PHP chính thức hỗ trợ tốt cho Laravel
FROM php:8.2-fpm

# Cài đặt các công cụ hệ thống cần thiết và bộ môi trường Python 3
RUN apt-get update && apt-get install -y \
    nginx \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    python3 \
    python3-pip \
    python3-venv

# Xóa cache hệ thống để giảm dung lượng
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Cài đặt các thư viện PHP cần thiết để kết nối MySQL (PDO)
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Cài đặt công cụ Composer mới nhất vào bên trong
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Thiết lập thư mục làm việc trong máy chủ ảo
WORKDIR /var/www

# Copy toàn bộ mã nguồn Laravel của bạn vào máy chủ
COPY . /var/www

# Kiểm tra nếu bạn có file requirements.txt thì tự động cài đặt thư viện Python luôn
RUN if [ -f /var/www/requirements.txt ] ; then pip3 install -r /var/www/requirements.txt --break-system-packages ; fi

# Cài đặt các gói thư viện PHP (Thêm cờ --no-scripts để tránh chạy lệnh artisan ngầm lúc build)
RUN composer install --no-interaction --optimize-autoloader --no-dev --no-scripts

# Cấp quyền đọc ghi file cho thư mục lưu trữ của Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Cấu hình cổng chạy mạng cho Web qua Nginx
COPY .nginx/nginx.conf /etc/nginx/sites-available/default

# LỆNH KHỞI CHẠY (QUAN TRỌNG): 
# Chúng ta đưa các lệnh xóa cache vào đây. Khi container khởi chạy thật trên Render, 
# lúc này đã có đủ Database và biến môi trường, lệnh chạy sẽ mượt mà 100%!
CMD php artisan config:clear && php artisan cache:clear && service nginx start && php-fpm