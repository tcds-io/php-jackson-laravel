#!/bin/bash

COMMIT_HASH="$1"
WORKDIR=tests/blog

rm -rf $WORKDIR

composer create-project laravel/laravel $WORKDIR
php tests/fix-composer.php

rm $WORKDIR/routes/web.php
rm $WORKDIR/bootstrap/providers.php

cp tests/fixtures/providers.php $WORKDIR/bootstrap/
cp tests/fixtures/web.php $WORKDIR/routes/
cp tests/fixtures/Foo.php $WORKDIR/app/Models/
cp tests/fixtures/Type.php $WORKDIR/app/Models/
cp tests/fixtures/FooBarController.php $WORKDIR/app/Http/Controllers/
cp tests/fixtures/HttpSerializationTest.php $WORKDIR/tests/Feature/
cp tests/fixtures/serializer.php $WORKDIR/config/

mkdir $WORKDIR/jackson
cp -r src/ $WORKDIR/jackson

composer require tcds-io/php-jackson --working-dir=$WORKDIR
