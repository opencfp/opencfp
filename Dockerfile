
ARG PHP_VERSION=7.4.1
ARG OPENRESTY_VERSION=1.17.8.2

FROM php:${PHP_VERSION}-fpm-alpine AS php

ARG CFP_ENV
ENV CFP_ENV=${CFP_ENV}

LABEL Description="This is the docker image for OpenCFP, a PHP-based conference talk submission system." \
      org.label-schema.name=opencfp_php   \
      org.label-schema.description="This is the docker image for OpenCFP, a PHP-based conference talk submission system." \
      org.label-schema.vcs-url="https://github.com/opencfp/opencfp.git" \
      org.label-schema.vendor="OpenCFP" \
      org.label-schema.schema-version="1.0.0"

# Adding the utilities tools to create the container
RUN apk add --no-cache \
    acl \
    fcgi \
    gettext \
    git \
    ttf-freefont \
    yarn \
   ;
RUN apk add --update nodejs nodejs-npm
RUN npm i -g cross-env

ARG ACPU_VERSION=5.1.17
ENV TRUST_PROXIES true


RUN set -eux ;\
    apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    icu-dev \
    libzip-dev \
    zlib-dev \
    ; \
    docker-php-ext-configure zip ; \
    docker-php-ext-install -j$(nproc) \
    intl \
    pdo_mysql \ 
    zip \
    ;\
    pecl install \
        apcu-${ACPU_VERSION} \
    ;\
    pecl clear-cache; \
    docker-php-ext-enable \
        apcu \
        opcache \
    ;\
    runDeps="$( \
        scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
        | tr ',' '\n' \
        | sort -u \
        | awk 'system("[ -e /usr/local/lib" $1 "]") == 0 { next } { print "so:" $1 }' \
        )";\
    apk add --no-cache --virtual .api-phpexts-rundeps $runDeps; \
    apk del .build-deps

# Copy the composer command to this images by using the composer images with latest tag
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN chmod +x /usr/bin/composer

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

# Clear cache before the installation to avoid conflict
RUN composer clear-cache
# Prevent the reinstallation of vendors at every changes in the source code
COPY composer.json composer.lock ./

# Install Symfony Flex globally to speed up download of Composer packages (parallelized prefetching)
RUN set -eux; \
    COMPOSER_MEMORY_LIMIT=-1 composer require "symfony/flex" --prefer-dist --no-progress --classmap-authoritative; \
    composer clear-cache
ENV PATH="${PATH}:/root/.composer/vendor/bin"


VOLUME /srv/app/log
WORKDIR /srv/app/

# Copy only specifically what we need
COPY bin bin/
RUN chmod +x bin/console
COPY config config/
COPY factories factories/
COPY migrations migrations/
COPY resources resources/
COPY src src/
COPY tests tests/
COPY web web/
COPY .php_cs.dist \
    app.json \
    migrations.php \
    mix-manifest.json \
    package.json \
    Procfile \
    tailwind.js \
    webpack.mix.js \
    yarn.lock \
    ./

COPY docker/php/conf.d/www.conf /usr/local/etc/php-fpm.d/www.conf
RUN mkdir -p /usr/local/log/ && chmod +x /usr/local/log;
COPY docker/php/conf.d/opencfp.${CFP_ENV}.ini /usr/local/etc/php/php.ini

COPY /docker/php/docker-healtcheck.sh /usr/local/bin/docker-healtcheck
RUN chmod +x /usr/local/bin/docker-healtcheck


HEALTHCHECK --interval=10s --timeout=3s --retries=3 CMD [ "docker-healtcheck" ]

COPY /docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint
ENTRYPOINT [ "docker-entrypoint" ]
CMD ["php-fpm"]


# "nginx" stage
# depends on the "php" stage above
# The OpenResty distribution of NGINX is only needed for Kubernetes compatiblity (dynamic upstream resolution)
FROM openresty/openresty:${OPENRESTY_VERSION}-alpine AS nginx


RUN echo -e "env UPSTREAM;\n$(cat /usr/local/openresty/nginx/conf/nginx.conf)" >  /usr/local/openresty/nginx/conf/nginx.conf


# Copy the default configuration of the nginx server to the default location
COPY docker/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf

# Creation of the log dir to avoid nginx throw an error of directories not found and adding the write right to all
RUN mkdir -p /var/log/nginx && chmod +x /var/log/nginx;

# Set the working directory to the root of the OpenCfp project and copy the files from the php stage above
WORKDIR /srv/app
COPY --from=php /srv/app ./
