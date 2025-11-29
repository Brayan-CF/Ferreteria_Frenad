# ============================================================================
# Dockerfile para Backend Laravel - Ferretería Frenad (SEGURO)
# Base: PHP 8.2 con FPM y extensiones necesarias
# SEGURIDAD: Usuario no-root con UID/GID sincronizado con host
# ============================================================================

FROM php:8.2-fpm-alpine

# ============================================================================
# ARGUMENTOS DE BUILD - Recibir UID/GID del host
# ============================================================================
ARG USER_ID=1000
ARG GROUP_ID=1000

# Metadata
LABEL maintainer="Ferreteria Frenad <dev@frenad.com>"
LABEL description="Backend API Laravel para Sistema POS (NON-ROOT)"

# Variables de entorno
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp \
    TZ=America/La_Paz \
    PHP_FPM_USER=laravel \
    PHP_FPM_GROUP=laravel

# ============================================================================
# INSTALAR DEPENDENCIAS DEL SISTEMA (solo build-time como root)
# ============================================================================
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
    shadow \
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

# ============================================================================
# CREAR USUARIO NO-ROOT CON MISMO UID/GID DEL HOST
# ============================================================================

# Eliminar usuario www-data por defecto si existe (UID 82)
RUN deluser www-data 2>/dev/null || true

# Crear grupo con GID del host
RUN addgroup -g ${GROUP_ID} laravel || echo "Grupo ya existe"

# Crear usuario con UID del host
RUN adduser -D -u ${USER_ID} -G laravel laravel || echo "Usuario ya existe"

# Verificar que el usuario se creó correctamente
RUN id laravel

# ============================================================================
# CONFIGURAR PHP-FPM PARA USAR USUARIO NO-ROOT
# ============================================================================

# Copiar archivos de configuración PHP
COPY ./php.ini /usr/local/etc/php/conf.d/custom.ini

# Configurar PHP-FPM para ejecutar como usuario laravel
RUN sed -i 's/user = www-data/user = laravel/g' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/group = www-data/group = laravel/g' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/listen.owner = www-data/listen.owner = laravel/g' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/listen.group = www-data/listen.group = laravel/g' /usr/local/etc/php-fpm.d/www.conf

# ============================================================================
# DIRECTORIO DE TRABAJO Y PERMISOS
# ============================================================================

WORKDIR /var/www/html

# Crear directorios necesarios con permisos correctos
RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R laravel:laravel /var/www/html

# ============================================================================
# CAMBIAR A USUARIO NO-ROOT (CRÍTICO PARA SEGURIDAD)
# ============================================================================

USER laravel

# ============================================================================
# EXPONER PUERTO (como usuario no-root)
# ============================================================================

EXPOSE 8000

# ============================================================================
# HEALTHCHECK
# ============================================================================

HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD php artisan --version || exit 1

# ============================================================================
# COMANDO DE INICIO (ejecutado como usuario laravel, NO root)
# ============================================================================

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]