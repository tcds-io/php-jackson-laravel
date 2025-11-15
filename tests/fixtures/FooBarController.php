<?php

namespace App\Http\Controllers;

use App\Models\Foo;

class FooBarController
{
    /**
     * @param list<Foo> $items
     * @return list<Foo>
     */
    public function list(array $items): array
    {
        return $items;
    }

    public function read(int $id, Foo $foo): Foo
    {
        return new Foo(
            id: $id,
            a: $foo->a,
            b: $foo->b,
            type: $foo->type,
        );
    }
}
