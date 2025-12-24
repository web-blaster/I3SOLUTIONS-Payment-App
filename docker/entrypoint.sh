#!/bin/sh
##set
#set it
set -e

MODE="${MODE:-web}"

if [ "$MODE" = "worker" ]; then
  echo "Starting Laravel SQS worker..."
  exec php artisan queue:work --sleep=3 --tries=3 --timeout=90 --no-interaction
fi

echo "Starting PHP-FPM + NGINX..."
php-fpm -D
exec nginx -g 'daemon off;'
