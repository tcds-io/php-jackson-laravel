<?php

namespace App\Models;

readonly class Foo
{
    public function __construct(
        public ?int $id = null,
        public string $a,
        public string $b,
        public Type $type,
    ) {
    }
}
