# ============================================================================
# Dockerfile para Frontend - Ferretería Frenad
# Base: Nginx Alpine (HTML/CSS/JS estático)
# ============================================================================

FROM nginx:alpine

# Metadata
LABEL maintainer="Ferreteria Frenad <dev@frenad.com>"
LABEL description="Frontend POS con Nginx"

# Variables de entorno
ENV TZ=America/La_Paz

# Instalar dependencias mínimas (solo timezone)
RUN apk add --no-cache \
    tzdata \
    && ln -snf /usr/share/zoneinfo/$TZ /etc/localtime \
    && echo $TZ > /etc/timezone

# Copiar configuración de Nginx
COPY ./nginx.conf /etc/nginx/conf.d/default.conf

# Directorio de trabajo
WORKDIR /usr/share/nginx/html

# Permisos
RUN chown -R nginx:nginx /usr/share/nginx/html \
    && chmod -R 755 /usr/share/nginx/html

# Exponer puerto
EXPOSE 80

# Healthcheck
HEALTHCHECK --interval=30s --timeout=3s --start-period=10s --retries=3 \
    CMD wget --quiet --tries=1 --spider http://localhost/ || exit 1

# Comando
CMD ["nginx", "-g", "daemon off;"]