#!/bin/bash

WORKDIR=tests/blog

echo "Deleting current blog installation..."
rm -rf $WORKDIR
echo "Done!"

composer create-project laravel/laravel $WORKDIR
composer config minimum-stability dev --working-dir=$WORKDIR

rm $WORKDIR/routes/web.php
mkdir $WORKDIR/app/Services
mkdir $WORKDIR/app/Queries

cp tests/fixtures/web.php $WORKDIR/routes/
cp tests/fixtures/Foo.php $WORKDIR/app/Models/
cp tests/fixtures/Type.php $WORKDIR/app/Models/
cp tests/fixtures/AuthTokenService.php $WORKDIR/app/Services/
cp tests/fixtures/InvoiceQuery.php $WORKDIR/app/Queries/
cp tests/fixtures/FooBarController.php $WORKDIR/app/Http/Controllers/
cp tests/fixtures/CallableSerializationTest.php $WORKDIR/tests/Feature/
cp tests/fixtures/ControllerSerializationTest.php $WORKDIR/tests/Feature/
cp tests/fixtures/serializer.php $WORKDIR/config/

composer config \
  repositories.php-jackson-laravel '{"type": "path", "url": "./../../", "options": {"symlink": true}}' \
  --working-dir=$WORKDIR

composer require tcds-io/php-jackson:dev-main tcds-io/php-jackson-laravel:* \
    --working-dir=$WORKDIR
