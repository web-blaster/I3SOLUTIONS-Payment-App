#!/bin/sh
set -e

MODE="${MODE:-web}"

if [ "$MODE" = "worker" ]; then
  echo "Starting Laravel SQS worker..."
  exec php artisan queue:work sqs --sleep=3 --tries=3 --timeout=90
fi

echo "Starting PHP-FPM + NGINX..."
php-fpm -D
exec nginx -g 'daemon off;'
