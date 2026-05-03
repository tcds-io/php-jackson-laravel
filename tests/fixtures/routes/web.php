<?php

use App\Http\Controllers\FooBarController;
use App\Models\Foo;
use App\Models\Greeting;
use App\Models\Type;
use Illuminate\Support\Facades\Route;
use Tcds\Io\Jackson\Laravel\Attributes\Inject;
use Tcds\Io\Jackson\Laravel\Attributes\Respond;

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
    #[Respond(statusCode: 201)]
    fn(#[Inject] Greeting $greeting): Greeting => $greeting,
);

Route::post('/controller', [FooBarController::class, 'list']);
Route::post('/controller/resource', [FooBarController::class, 'resource']);
Route::post('/controller/invoices', [FooBarController::class, 'invoices']);
Route::post('/controller/greet', [FooBarController::class, 'greet']);
Route::post('/controller/{id}', [FooBarController::class, 'read']);
