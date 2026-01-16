#!/bin/bash

WORKDIR=tests/blog

echo "Deleting current blog installation..."
rm -rf $WORKDIR
echo "Done!"

composer create-project laravel/laravel $WORKDIR
composer config minimum-stability dev --working-dir=$WORKDIR

cp -r tests/fixtures/. $WORKDIR

composer config \
  repositories.php-jackson-laravel '{"type": "path", "url": "./../../", "options": {"symlink": true}}' \
  --working-dir=$WORKDIR

composer require tcds-io/php-jackson \
    tcds-io/php-jackson-laravel:* \
    --working-dir=$WORKDIR

composer install \
    --no-ansi \
    --no-dev \
    --no-interaction \
    --no-plugins \
    --no-progress \
    --no-scripts \
    --optimize-autoloader
