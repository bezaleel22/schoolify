FROM webdevops/php-nginx:8.2-alpine
ENV DOCUMENT_ROOT=/var/www/html
ENV WEB_DOCUMENT_ROOT ${DOCUMENT_ROOT}
ENV PHP_UPLOAD_MAX_FILESIZE: 100M
ENV PHP_POST_MAX_SIZE: 120M
ENV PHP_DISMOD=bz2,calendar,exiif,ffi,intl,gettext,ldap,imap,pdo_pgsql,pgsql,soap,sockets,sysvmsg,sysvsm,sysvshm,shmop,apcu,vips,yaml,mongodb,amqp

WORKDIR ${DOCUMENT_ROOT}

# Copy Composer binary from the Composer official Docker image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY --chown=application:application . .
USER application

RUN composer install --no-interaction --no-plugins --no-scripts --no-dev --prefer-dist --optimize-autoloader \
    && chmod -R 775 storage bootstrap/cache \
    && php artisan key:generate --force \
    && php artisan view:clear \
    && php artisan cache:clear \
    && php artisan route:clear \
    && php artisan config:clear