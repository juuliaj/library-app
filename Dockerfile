FROM php:8.4-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libsqlite3-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_sqlite

# Serve the app from src/ instead of the default htdocs root.
ENV APACHE_DOCUMENT_ROOT=/var/www/app/src
RUN sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf \
    && sed -ri -e "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf

WORKDIR /var/www/app
COPY . .
RUN chmod +x docker-entrypoint.sh

ENTRYPOINT ["/var/www/app/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
