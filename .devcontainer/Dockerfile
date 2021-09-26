FROM alpine:3.14

COPY --from=symfonycorp/cli /symfony /usr/bin/symfony
COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN apk add --no-cache \
    coreutils \
    gettext \
    php8-ctype \
    php8-curl \
    php8-dom \
    php8-gd \
    php8-iconv \
    php8-json \
    php8-intl \
    php8-fileinfo\
    php8-mbstring \
    php8-opcache \
    php8-openssl \
    php8-pdo \
    php8-pdo_sqlite \
    php8-xml \
    php8-xsl \
    php8-zlib \
    php8-phar \
    php8-tokenizer \
    php8-session \
    php8-simplexml \
    php8-zip \
    php8-xmlwriter \
    php8-sodium \
    php8-pecl-apcu \
    make \
    git \
    curl

RUN ln -s /usr/bin/php8 /usr/bin/php

ARG USERNAME=user
ARG UID=1000
ARG GID=1000
RUN addgroup -g $GID $USERNAME
RUN adduser -u $UID -G $USERNAME -s /bin/sh -D $USERNAME
USER $USERNAME
