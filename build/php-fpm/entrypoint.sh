#!/bin/sh

php8 /app/bin/console cache:warmup
php8 /app/bin/console doctrine:database:create
cp /db/data.db /db/data.db.bak
php8 /app/bin/console doctrine:migrations:migrate --no-interaction

exec $@
