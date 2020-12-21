ARG PHP_VERSION=7.4.1
ARG OPENRESTY_VERSION=1.17.8.2
ARG NODE_VERSION=15.1.0

# "nodejs" stage
FROM node:${NODE_VERSION}-alpine AS nodejs

RUN apk add --no-cache yarn

RUN apk add --update nodejs nodejs-npm
RUN npm i -g cross-env

WORKDIR /srv/app/

COPY package.json tailwind.js yarn.lock webpack.mix.js ./
RUN set -eux; \
	yarn install;

COPY resources resources/
COPY web web/

VOLUME /srv/app/node_modules

CMD [ "yarn", "run", "production" ]

# "php" stage
# depends on the "nodejs" stage above
FROM php:${PHP_VERSION}-fpm-alpine AS php

# Adding the utilities tools to create the container
RUN apk add --no-cache \
	acl \
	fcgi \
	gettext \
	git \
	ttf-freefont \
	;

ARG ACPU_VERSION=5.1.17

RUN set -eux ;\
	apk add --no-cache --virtual .build-deps \
		$PHPIZE_DEPS \
		icu-dev \
		libzip-dev \
		zlib-dev \
		freetype \
		libpng \
		libjpeg-turbo \
		freetype-dev \
		libpng-dev \
		libjpeg-turbo-dev  \
	; \
	docker-php-ext-configure gd \
		--with-freetype \
		--with-jpeg  \
	; \
	docker-php-ext-configure zip ; \
	docker-php-ext-install -j$(nproc) \
		intl \
		pdo_mysql \
		zip \
		gd \
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

# Copy the composer command to this images by using the composer images with the version tag of 1
COPY --from=composer:1 /usr/bin/composer /usr/bin/composer
RUN chmod +x /usr/bin/composer

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

# Install Symfony Flex globally to speed up download of Composer packages (parallelized prefetching)
RUN set -eux; \
	COMPOSER_MEMORY_LIMIT=-1 composer global require "symfony/flex" --prefer-dist --no-progress --classmap-authoritative; \
	composer clear-cache
ENV PATH="${PATH}:/root/.composer/vendor/bin"

WORKDIR /srv/app/

ARG CFP_ENV=production
ENV CFP_ENV=${CFP_ENV}
ENV TRUST_PROXIES true


# Copy only specifically what we need
COPY composer.json composer.lock ./
RUN set -eux; \
	composer install --prefer-dist --no-dev --no-scripts --no-progress --no-suggest; \
	composer clear-cache

COPY bin bin/
# RUN chmod +x bin/console
COPY config config/
COPY factories factories/
COPY migrations migrations/
COPY --from=nodejs /srv/app/resources resources/
COPY src src/
COPY tests tests/
COPY --from=nodejs /srv/app/web web/
COPY .php_cs.dist migrations.php mix-manifest.json ./

RUN set -eux; \
	mkdir -p var/cache var/log; \
	composer dump-autoload --classmap-authoritative --no-dev; \
	composer run-script --no-dev post-install-cmd; \
	chmod +x bin/console; sync

RUN mkdir -p /usr/local/log/ && chmod +x /usr/local/log;
COPY docker/php/conf.d/opencfp.${CFP_ENV}.ini /usr/local/etc/php/php.ini

VOLUME /srv/app/log
VOLUME /srv/app/cache

COPY docker/php/docker-healtcheck.sh /usr/local/bin/docker-healtcheck
RUN chmod +x /usr/local/bin/docker-healtcheck

HEALTHCHECK --interval=10s --timeout=3s --retries=3 CMD [ "docker-healtcheck" ]

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]


# "nginx" stage
# depends on the "php" stage above
# The OpenResty distribution of NGINX is only needed for Kubernetes compatiblity (dynamic upstream resolution)
FROM openresty/openresty:${OPENRESTY_VERSION}-alpine AS nginx

RUN echo -e "env UPSTREAM;\n$(cat /usr/local/openresty/nginx/conf/nginx.conf)" >  /usr/local/openresty/nginx/conf/nginx.conf

# Copy the default configuration of the nginx server to the default location
COPY docker/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf

# Set the working directory to the root of the OpenCfp project and copy the files from the php stage above
WORKDIR /srv/app
COPY --from=php /srv/app/web web
