<?php

namespace App\Http\Controllers;

use App\Models\Foo;
use App\Models\Greeting;
use App\Queries\InvoiceQuery;
use Tcds\Io\Jackson\Laravel\Attributes\Inject;
use Tcds\Io\Jackson\Laravel\Attributes\JacksonResponse;
use Tcds\Io\Jackson\Laravel\Http\JacksonResponse as JacksonHttpResponse;

use function Tcds\Io\Jackson\Laravel\jackson;

class FooBarController
{
    #[JacksonResponse(status: 201, headers: ['X-Jackson' => 'attribute'])]
    public function greet(#[Inject] Greeting $greeting): Greeting
    {
        return $greeting;
    }

    public function greetResponse(#[Inject] Greeting $greeting): JacksonHttpResponse
    {
        return jackson($greeting)
            ->status(201)
            ->header('X-Jackson', 'fluent');
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

    /**
     * @return array{name: ?string}
     */
    public function nullable(?string $name): array
    {
        return ['name' => $name];
    }

    public function invoices(InvoiceQuery $query): InvoiceQuery
    {
        return $query;
    }
}
