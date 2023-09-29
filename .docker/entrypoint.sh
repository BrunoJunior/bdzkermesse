#!/usr/bin/env bash

composer install -n
php ./bin/console doctrine:migrations:migrate --no-interaction
php ./bin/console doc:fix:load --no-interaction

exec "$@"