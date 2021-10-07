FROM ghcr.io/bullshitsoftware/doxter-php:latest

RUN apk add --no-cache php8-fpm

COPY build/php-fpm/app.ini /etc/php8/conf.d/
COPY build/php-fpm/app.ini /etc/php8/cli/conf.d/
COPY build/php-fpm/app.pool.conf /etc/php8/php-fpm.d/www.conf
COPY build/php-fpm/entrypoint.sh /app/entrypoint.sh
RUN chmod +x /app/entrypoint.sh

COPY bin /app/bin
COPY config /app/config
COPY migrations /app/migrations
COPY public /app/public
COPY src /app/src
COPY templates /app/templates
COPY vendor /app/vendor
COPY composer.json /app/composer.json
COPY composer.lock /app/composer.lock
COPY symfony.lock /app/symfony.lock
RUN touch /app/.env

ARG USERNAME=user
ARG UID=1000
ARG GID=1000
RUN addgroup -g $GID $USERNAME
RUN adduser -u $UID -G $USERNAME -s /bin/sh -D $USERNAME

RUN chown -R $USERNAME:$USERNAME /var/log/php8
RUN mkdir /app/var /db
RUN chown -R $USERNAME:$USERNAME /app/var /db

USER $USERNAME

WORKDIR /app

ENV APP_ENV=prod
ENV DATABASE_URL=sqlite:////db/data.db

CMD [ "/app/entrypoint.sh" ]

EXPOSE 9001
