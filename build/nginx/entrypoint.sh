#!/bin/sh

envsubst "$(printf '${%s} ' $(env | cut -d= -f1))" < /nginx/app.conf.template > /nginx/app.conf

exec $@
