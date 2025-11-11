<?php

use App\Http\Controllers\FooBarController;
use App\Models\Foo;
use App\Models\Type;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return new Foo(id: 1, a: "aaa", b: "get", type: Type::AAA);
});

Route::post('/', function (Foo $foo) {
    return $foo;
});

Route::post('/controller', [FooBarController::class, 'list']);
Route::post('/controller/{id}', [FooBarController::class, 'read']);
