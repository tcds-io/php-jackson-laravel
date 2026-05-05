<?php

namespace App\Http\Controllers;

use App\Models\Foo;
use App\Models\Greeting;
use App\Queries\InvoiceQuery;
use App\Services\AuthTokenService;
use Tcds\Io\Jackson\Laravel\Attributes\JacksonInject;
use Tcds\Io\Jackson\Laravel\Attributes\JacksonResponse;
use Tcds\Io\Jackson\Laravel\Http\JacksonResponse as JacksonHttpResponse;

use function Tcds\Io\Jackson\Laravel\jackson;

class FooBarController
{
    #[JacksonResponse(status: 201, headers: ['X-Jackson' => 'attribute'])]
    public function greet(#[JacksonInject] Greeting $greeting): Greeting
    {
        return $greeting;
    }

    public function greetResponse(#[JacksonInject] Greeting $greeting): JacksonHttpResponse
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
    public function missingNullable(?string $name = null): array
    {
        return ['name' => $name];
    }

    /**
     * @return array{sort: string}
     */
    public function defaulted(string $sort = 'created_at'): array
    {
        return ['sort' => $sort];
    }

    /**
     * @return array{userId: int}
     */
    public function service(AuthTokenService $authTokenService): array
    {
        return $authTokenService->getClaims();
    }

    public function invoices(InvoiceQuery $query): InvoiceQuery
    {
        return $query;
    }
}
