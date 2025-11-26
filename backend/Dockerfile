# ============================================================================
# Dockerfile para Backend Laravel - Ferretería Frenad
# Base: PHP 8.2 con FPM y extensiones necesarias
# ============================================================================

FROM php:8.2-fpm-alpine

# Argumentos de usuario
ARG USER_ID=1000
ARG GROUP_ID=1000

# Metadata
LABEL maintainer="Ferreteria Frenad <dev@frenad.com>"
LABEL description="Backend API Laravel para Sistema POS"

# Variables de entorno
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp \
    TZ=America/La_Paz

# Instalar dependencias del sistema
RUN apk add --no-cache \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    postgresql-dev \
    oniguruma-dev \
    libzip-dev \
    icu-dev \
    tzdata \
    bash \
    vim \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        mbstring \
        zip \
        exif \
        pcntl \
        bcmath \
        gd \
        intl

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar zona horaria
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Crear usuario no-root
RUN addgroup -g ${GROUP_ID} laravel \
    && adduser -D -u ${USER_ID} -G laravel laravel

# Directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos de configuración PHP
COPY ./php.ini /usr/local/etc/php/conf.d/custom.ini

# Cambiar permisos
RUN chown -R laravel:laravel /var/www/html

# Cambiar a usuario no-root
USER laravel

# Exponer puerto
EXPOSE 8000

# Healthcheck
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD php artisan --version || exit 1

# Comando por defecto
CMD ["php-fpm"]