#!/bin/sh

php8 /app/bin/console cache:warmup
php8 /app/bin/console doctrine:database:create
php8 /app/bin/console doctrine:migrations:migrate --no-interaction

exec php-fpm8 -F -O
