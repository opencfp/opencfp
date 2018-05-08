FROM zaherg/php-and-nginx:latest

ARG BUILD_DATE
ARG DOCKER_REPO
ARG VCS_REF
ARG IMAGE_NAME
ARG VERSION

LABEL Maintainer="Zaher Ghaibeh <z@zah.me>" \
      Description="This is the docker image for OpenCFP, a PHP-based conference talk submission system." \
      org.label-schema.name=$IMAGE_NAME \
      org.label-schema.description="This is the docker image for OpenCFP, a PHP-based conference talk submission system." \
      org.label-schema.build-date=$BUILD_DATE \
      org.label-schema.vcs-url="https://github.com/opencfp/opencfp.git" \
      org.label-schema.vcs-ref=$VCS_REF \
      org.label-schema.vendor="OpenCFP" \
      org.label-schema.version=$VERSION \
      org.label-schema.schema-version="1.0.0"

ENV CFP_ENV ${CFP_ENV:-development}
ENV CFP_DB_HOST ${CFP_DB_HOST:-"127.0.0.1"}
ENV CFP_DB_PASS ${CFP_DB_PASS:-root}

COPY ./ /var/www

RUN set -ex \
    && sed -i "s/access_log  logs\/access.log  main;/access_log  \/var\/log\/nginx\/access.cfp.log; error_log \/var\/log\/nginx\/error.cfp.log;/" /etc/nginx/nginx.conf \
    && sed -i "s/\/web\/html/\/var\/www\/web/" /etc/nginx/nginx.conf && \
    sed -i "s/localhost/_/" /etc/nginx/nginx.conf && \
    sed -i "s/#user  nobody;/user  www-data;/" /etc/nginx/nginx.conf && \
    sed -i "s/#gzip  on;/gzip  on;/" /etc/nginx/nginx.conf && \
    touch /usr/local/etc/php/php.ini && \
    echo "cgi.fix_pathinfo=0" >> /usr/local/etc/php/php.ini  && \
    echo "upload_max_filesize = 120M" >> /usr/local/etc/php/php.ini && \
    echo "post_max_size = 120M" >> /usr/local/etc/php/php.ini && \
    echo "memory_limit = 512M" >> /usr/local/etc/php/php.ini && \
    echo 'variables_order = "EGPCS"' >> /usr/local/etc/php/php.ini && \
    sed -i -e "s/;catch_workers_output\s*=\s*yes/catch_workers_output = yes/g" /usr/local/etc/php-fpm.d/www.conf && \
    sed -i -e "s/pm.max_children = 5/pm.max_children = 9/g" /usr/local/etc/php-fpm.d/www.conf && \
    sed -i -e "s/pm.start_servers = 2/pm.start_servers = 3/g" /usr/local/etc/php-fpm.d/www.conf && \
    sed -i -e "s/pm.min_spare_servers = 1/pm.min_spare_servers = 2/g" /usr/local/etc/php-fpm.d/www.conf && \
    sed -i -e "s/pm.max_spare_servers = 3/pm.max_spare_servers = 4/g" /usr/local/etc/php-fpm.d/www.conf && \
    sed -i -e "s/pm.max_requests = 500/pm.max_requests = 200/g" /usr/local/etc/php-fpm.d/www.conf && \
    mkdir -p /var/www/cache && \
    mkdir -p /var/www/cache/testing/sessions /var/www/cache/production/sessions /var/www/cache/development/sessions \
            /var/www/cache/testing/twig /var/www/cache/production/twig /var/www/cache/development/twig && \
    chgrp -R www-data /var/lib/nginx /var/www/web/uploads /var/www/cache /var/www/log && \
    chmod -R ug+rwx /var/lib/nginx /var/www/web/uploads /var/www/cache /var/www/log && \
    cp /var/www/config/$CFP_ENV.yml.dist /var/www/config/$CFP_ENV.yml && \
    rm -fr /var/www/html /var/www/localhost

COPY ./.docker/script/ /var/www/script/

WORKDIR /var/www

RUN composer global require hirak/prestissimo && \
    composer install --no-progress --no-suggest --prefer-dist --optimize-autoloader
