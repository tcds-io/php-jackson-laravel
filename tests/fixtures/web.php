<?php

use App\Http\Controllers\FooBarController;
use App\Models\Foo;
use App\Models\Type;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => response()->json(['foo' => 'bar']));
Route::get('/callable/resource/{id}', fn(int $id) => new Foo(id: $id, a: "aaa", b: "get", type: Type::AAA));
Route::post('/callable/resource', fn(Foo $foo) => $foo);

Route::post('/callable',
    /**
     * @param list<Foo> $items
     * @return list<Foo>
     */
    fn(array $items): array => $items,
);

Route::post('/controller', [FooBarController::class, 'list']);
Route::post('/controller/resource', [FooBarController::class, 'resource']);
Route::post('/controller/invoices', [FooBarController::class, 'invoices']);
Route::post('/controller/{id}', [FooBarController::class, 'read']);
