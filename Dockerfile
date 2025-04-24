FROM php:8.2-apache

# Install required PHP extensions and other dependencies
RUN apt-get update && apt-get install -y \
    curl \
    libzip-dev \
    cron \
    && docker-php-ext-install mysqli pdo pdo_mysql zip opcache \
    && a2enmod rewrite headers

# Configure PHP
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Set up cron job for maintenance
RUN echo "0 1 * * * cd /var/www/html/maintenance && /usr/local/bin/php db-maintenance.php >> /var/log/cron.log 2>&1" > /etc/cron.d/maintenance
RUN chmod 0644 /etc/cron.d/maintenance
RUN crontab /etc/cron.d/maintenance

# Set working directory
WORKDIR /var/www/html

# Create required directories
RUN mkdir -p /var/www/html/json/reports/cache

# Copy application files
COPY . /var/www/html/

# Create config.php from template
RUN cp config-template.php config.php

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/json/reports/cache

# Start cron in background
CMD service cron start && apache2-foreground

# Expose port 80
EXPOSE 80 