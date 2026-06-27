#!/bin/sh

php artisan optimize:clear

php artisan reverb:start \
    --host=0.0.0.0 \
    --port=$PORT