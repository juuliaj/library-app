#!/bin/sh
set -e

php /var/www/app/init_db.php

chown -R www-data:www-data /var/www/data

exec "$@"
