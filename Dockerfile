FROM dunglas/frankenphp

# Add PostgreSQL and other PHP extensions
RUN install-php-extensions \
    pdo_pgsql \
    gd \
    intl \
    zip \
    opcache \