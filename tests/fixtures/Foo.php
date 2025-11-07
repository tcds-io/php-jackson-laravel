<?php

namespace App\Models;

readonly class Foo
{
    public function __construct(
        public string $a,
        public string $b,
        public Type $type,
    ) {
    }
}
