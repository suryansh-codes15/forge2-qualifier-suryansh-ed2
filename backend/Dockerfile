FROM serversideup/php:8.2-fpm-nginx

# Set the document root for Nginx to Laravel's public directory
ENV DOCUMENT_ROOT=/var/www/html/public

# Set system environment variables
ENV APP_ENV=production
ENV APP_DEBUG=true

# Copy application files and set ownership to webuser
COPY --chown=webuser:webgroup . /var/www/html

# Install composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Create and initialize SQLite database during build
RUN touch database/database.sqlite && \
    php artisan migrate --force && \
    php artisan db:seed --force && \
    chown -R webuser:webgroup database storage bootstrap/cache
