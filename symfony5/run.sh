#!/bin/sh
set -e
service php7.4-fpm start
composer install
# Import the database structure into testing database too.
echo y | php bin/console doctrine:migrations:migrate --env=test
echo y | php bin/console doctrine:migrations:migrate

service nginx start && tail -F /var/log/nginx/error.log
