#!/bin/sh

php artisan migrate --force

php artisan optimize
npm run dev
php artisan serve \
--host=0.0.0.0 \
--port=8080