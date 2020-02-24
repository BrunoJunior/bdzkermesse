#!/usr/bin/env bash
git pull
sudo -u www-data composer install --no-dev --optimize-autoloader --classmap-authoritative
sudo -u www-data APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear
sudo -u www-data php bin/console doctrine:migrations:migrate
chown www-data:www-data -R ./*
server apache2 restart
