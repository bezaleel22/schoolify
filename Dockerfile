FROM webdevops/php-nginx:8.2-alpine

ENV DOCUMENT_ROOT=/app
ENV WEB_DOCUMENT_ROOT=${DOCUMENT_ROOT}
ENV PHP_DISMOD=bz2,exif,ffi,gettext,ldap,imap,pdo_pgsql,pgsql,soap,sockets,sysvmsg,sysvsm,sysvshm,shmop,apcu,vips,yaml,mongodb,amqp

WORKDIR ${DOCUMENT_ROOT}
RUN echo post_max_size = 120M >> /opt/docker/etc/php/php.ini

COPY --chown=application:www-data . .
COPY docker/worker.conf /opt/docker/etc/supervisor.d/worker.conf
# COPY docker/default.conf /opt/docker/etc/nginx/vhost.conf

USER application

RUN composer install --no-interaction --no-plugins --no-scripts --no-dev --prefer-dist --optimize-autoloader \
    && chmod -R 775 storage bootstrap/cache \
    && php artisan key:generate --force \
    && php artisan optimize \
    && php artisan app:setup

EXPOSE 80
