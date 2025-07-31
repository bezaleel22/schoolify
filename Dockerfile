FROM webdevops/php-nginx:8.2-alpine

ENV DOCUMENT_ROOT=/app
ENV WEB_DOCUMENT_ROOT=${DOCUMENT_ROOT}
ENV PHP_DISMOD=bz2,exif,ffi,gettext,ldap,imap,pdo_pgsql,pgsql,soap,sockets,sysvmsg,sysvsm,sysvshm,shmop,apcu,vips,yaml,mongodb,amqp

WORKDIR ${DOCUMENT_ROOT}
RUN echo post_max_size = 120M >> /opt/docker/etc/php/php.ini

# Install PrinceXML dependencies and Alpine-compatible version
# RUN apk add --no-cache curl giflib libavif fontconfig ttf-dejavu \
#     && curl -L -o prince-16.1-alpine3.22-x86_64.tar.gz \
#        "https://www.princexml.com/download/prince-16.1-alpine3.22-x86_64.tar.gz" \
#     && tar -xzf prince-16.1-alpine3.22-x86_64.tar.gz \
#     && mkdir -p prince/bin \
#     && mv prince-16.1-alpine3.22-x86_64/lib/prince/bin/prince prince/bin/ \
#     && mv prince-16.1-alpine3.22-x86_64/lib/prince/bin/princedebug prince/bin/ \
#     && cp -r prince-16.1-alpine3.22-x86_64/lib/prince/{dict,dtd,fonts,hyph,icc,js,lang,lib,license,man,math,style} prince/ \
#     && chmod +x prince/bin/prince prince/bin/princedebug \
#     && rm -rf prince-16.1-alpine3.22-x86_64 prince-16.1-alpine3.22-x86_64.tar.gz

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
