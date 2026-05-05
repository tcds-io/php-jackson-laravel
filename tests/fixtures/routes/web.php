<?php

use App\Http\Controllers\FooBarController;
use App\Models\Foo;
use App\Models\Greeting;
use App\Models\Type;
use Illuminate\Support\Facades\Route;
use Tcds\Io\Jackson\Laravel\Attributes\Inject;
use Tcds\Io\Jackson\Laravel\Attributes\JacksonResponse;

use function Tcds\Io\Jackson\Laravel\jackson;

Route::get('/', fn() => response()->json(['foo' => 'bar']));
Route::get('/callable/resource/{id}', fn(int $id) => new Foo(id: $id, a: 'aaa', b: 'get', type: Type::AAA));
Route::post('/callable/resource', fn(Foo $foo) => $foo);

Route::post(
    '/callable',
    /**
     * @param list<Foo> $items
     * @return list<Foo>
     */
    fn(array $items): array => $items,
);

Route::post(
    '/callable/greet',
    #[JacksonResponse(status: 201, headers: ['X-Jackson' => 'attribute'])]
    fn(#[Inject] Greeting $greeting): Greeting => $greeting,
);

Route::post(
    '/callable/greet-response',
    fn(#[Inject] Greeting $greeting) => jackson($greeting)
        ->status(201)
        ->header('X-Jackson', 'fluent'),
);

Route::post('/controller', [FooBarController::class, 'list']);
Route::post('/controller/resource', [FooBarController::class, 'resource']);
Route::post('/controller/invoices', [FooBarController::class, 'invoices']);
Route::post('/controller/greet', [FooBarController::class, 'greet']);
Route::post('/controller/greet-response', [FooBarController::class, 'greetResponse']);
Route::post('/controller/{id}', [FooBarController::class, 'read']);
