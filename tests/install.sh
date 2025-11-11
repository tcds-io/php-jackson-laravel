#!/bin/bash

WORKDIR=tests/blog

rm -rf $WORKDIR

composer create-project laravel/laravel $WORKDIR
composer config minimum-stability dev --working-dir=$WORKDIR

rm $WORKDIR/routes/web.php

cp tests/fixtures/web.php $WORKDIR/routes/
cp tests/fixtures/Foo.php $WORKDIR/app/Models/
cp tests/fixtures/Type.php $WORKDIR/app/Models/
cp tests/fixtures/FooBarController.php $WORKDIR/app/Http/Controllers/
cp tests/fixtures/HttpSerializationTest.php $WORKDIR/tests/Feature/
cp tests/fixtures/serializer.php $WORKDIR/config/

composer config \
  repositories.php-jackson-laravel '{"type": "path", "url": "./../../", "options": {"symlink": true}}' \
  --working-dir=$WORKDIR

composer require tcds-io/php-jackson:dev-main tcds-io/php-jackson-laravel:* \
    --working-dir=$WORKDIR
