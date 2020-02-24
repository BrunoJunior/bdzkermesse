#!/usr/bin/env bash
git pull
sudo -u www-data composer install --no-dev --optimize-autoloader --classmap-authoritative
sudo -u www-data APP_ENV=prod APP_DEBUG=0 php bin/console --no-interaction cache:clear
sudo -u www-data php bin/console --no-interaction doctrine:migrations:migrate
chown www-data:www-data -R ./*
service apache2 restart
