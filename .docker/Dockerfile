FROM php:7.1-fpm

RUN curl -sS https://getcomposer.org/installer | php \
  && mv composer.phar /usr/local/bin/composer \
  && echo 'PATH="~/.composer/vendor/bin:$PATH"' >> ~/.bashrc

RUN curl -sL https://deb.nodesource.com/setup_9.x | bash - \
  && apt-get install -y nodejs \
  && npm install -g yarn

RUN apt-get update \
  && apt-get install -y --no-install-recommends \
    sudo \
    ssh \
    curl \
    dos2unix \
    git \
    make \
    vim \
    nano \
    wget \
    libmemcached-dev \
    libz-dev \
    libpq-dev \
    libjpeg-dev \
    libpng12-dev \
    libfreetype6-dev \
    libssl-dev \
    libmcrypt-dev \
    libmagickwand-dev \
    imagemagick \
  && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install mcrypt \
  && docker-php-ext-install pdo_mysql \
  && docker-php-ext-install pdo_pgsql \
  && docker-php-ext-configure gd \
    --enable-gd-native-ttf \
    --with-jpeg-dir=/usr/lib \
    --with-freetype-dir=/usr/include/freetype2 \
  && docker-php-ext-install gd \
  && pecl install imagick \
  && docker-php-ext-enable imagick \
  && docker-php-ext-install zip \
  && docker-php-ext-install bcmath 

ARG DEV_USER
ARG DEV_USER_ID
RUN useradd -u $DEV_USER_ID -m -r $DEV_USER && \
  echo "$DEV_USER ALL=(ALL) NOPASSWD: ALL" > /etc/sudoers
USER $DEV_USER

WORKDIR /usr/src/app
