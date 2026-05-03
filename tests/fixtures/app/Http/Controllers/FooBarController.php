<?php

namespace App\Http\Controllers;

use App\Models\Foo;
use App\Models\Greeting;
use App\Queries\InvoiceQuery;
use Tcds\Io\Jackson\Laravel\Attributes\Inject;
use Tcds\Io\Jackson\Laravel\Attributes\Respond;

class FooBarController
{
    #[Respond(statusCode: 201)]
    public function greet(#[Inject] Greeting $greeting): Greeting
    {
        return $greeting;
    }


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

    public function invoices(InvoiceQuery $query): InvoiceQuery
    {
        return $query;
    }
}
