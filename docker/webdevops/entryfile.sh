#!/usr/bin/env bash

if ${PULLPREVIEW:-false}; then
    php /var/www/html/artisan migrate --force
    su -c "php /var/www/html/artisan key:generate --force" application

    if ${PULLPREVIEW_FIRST_RUN:-false}; then
        php /var/www/html/artisan db:seed --force
    fi
fi

exec /entrypoint supervisord "$@"
