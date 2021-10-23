FROM ghcr.io/bullshitsoftware/doxter-php:latest

RUN apk add --no-cache php8-fpm

COPY build/php-fpm/app.ini /etc/php8/conf.d/
COPY build/php-fpm/app.ini /etc/php8/cli/conf.d/
COPY build/php-fpm/app.pool.conf /etc/php8/php-fpm.d/www.conf

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

RUN mkdir /app/var /db && chmod 777 /app/var /db

USER $USERNAME

WORKDIR /app

ENV APP_ENV=prod
ENV DATABASE_URL=sqlite:////db/data.db

COPY build/php-fpm/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT [ "/entrypoint.sh" ]
CMD [ "php-fpm8", "-F", "-O" ]

EXPOSE 9001
