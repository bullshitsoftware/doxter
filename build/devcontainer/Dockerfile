FROM ghcr.io/bullshitsoftware/doxter-php:latest

LABEL org.opencontainers.image.source="https://github.com/bullshitsoftware/doxter"

COPY --from=symfonycorp/cli /symfony /usr/bin/symfony
COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN apk add --no-cache php8-xdebug make git nodejs npm sqlite curl

COPY build/devcontainer/50_xdebug.ini /etc/php8/conf.d/50_xdebug.ini
RUN ln -s /usr/bin/php8 /usr/bin/php

RUN curl -L https://cs.symfony.com/download/php-cs-fixer-v3.phar -o /usr/bin/php-cs-fixer
RUN chmod +x /usr/bin/php-cs-fixer
RUN npm install -g prettier prettier-plugin-twig-melody

ARG USERNAME=user
ARG UID=1000
ARG GID=1000
RUN addgroup -g $GID $USERNAME
RUN adduser -u $UID -G $USERNAME -s /bin/sh -D $USERNAME
USER $USERNAME
