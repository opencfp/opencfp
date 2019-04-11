FROM php:7-fpm-alpine

LABEL Description="This is the docker image for OpenCFP, a PHP-based conference talk submission system." \
      org.label-schema.name=$IMAGE_NAME \
      org.label-schema.description="This is the docker image for OpenCFP, a PHP-based conference talk submission system." \
      org.label-schema.build-date=$BUILD_DATE \
      org.label-schema.vcs-url="https://github.com/opencfp/opencfp.git" \
      org.label-schema.vcs-ref=$VCS_REF \
      org.label-schema.vendor="OpenCFP" \
      org.label-schema.version=$VERSION \
      org.label-schema.schema-version="1.0.0"

RUN docker-php-ext-install pdo_mysql

ENV CFP_ENV ${CFP_ENV:-development}
ENV CFP_DB_HOST ${CFP_DB_HOST:-"127.0.0.1"}
ENV CFP_DB_PASS ${CFP_DB_PASS:-root}

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer