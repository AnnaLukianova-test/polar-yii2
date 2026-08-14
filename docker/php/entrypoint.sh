#!/bin/bash
set -e

cd /var/www/html/app

mkdir -p runtime/cache runtime/logs web/assets

if [ -L vendor ]; then
    rm vendor
fi

composer install --no-interaction --prefer-dist --no-scripts

php yii migrate --interactive=0

php-fpm -D
nginx -g 'daemon off;'
