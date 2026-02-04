# Production Cloudlog image — PHP 8.2 + Apache with source baked in
FROM php:8.2-apache

WORKDIR /var/www/html

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    libxml2-dev \
    libzip-dev \
    zlib1g-dev \
    libpng-dev \
    libonig-dev \
    mariadb-client \
    curl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install mysqli \
    && docker-php-ext-install gd \
    && docker-php-ext-install mbstring \
    && docker-php-ext-install zip \
    && docker-php-ext-install xml \
    && docker-php-ext-install curl \
    && a2enmod rewrite

# PHP tuning for file uploads and long operations
RUN echo "upload_max_filesize = 30M" >> /usr/local/etc/php/conf.d/cloudlog.ini \
    && echo "post_max_size = 35M" >> /usr/local/etc/php/conf.d/cloudlog.ini \
    && echo "memory_limit = 128M" >> /usr/local/etc/php/conf.d/cloudlog.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/cloudlog.ini \
    && echo "max_input_time = 300" >> /usr/local/etc/php/conf.d/cloudlog.ini

# Copy application source (baked into image, not volume-mounted)
COPY . /var/www/html

# Deploy .htaccess for clean URLs
RUN cp /var/www/html/.htaccess.sample /var/www/html/.htaccess

# Install entrypoint
COPY portainer/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh && chmod +x /usr/local/bin/entrypoint.sh

# Set base ownership
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
