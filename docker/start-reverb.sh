#!/bin/sh

set -e

php artisan optimize:clear

echo "PORT=$PORT"

exec php artisan reverb:start \
    --host=0.0.0.0 \
    --port="${PORT:-8080}"