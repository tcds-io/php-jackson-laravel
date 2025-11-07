<?php

use App\Models\Foo;
use App\Models\Type;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return new Foo(a: "aaa", b: "get", type: Type::AAA);
});

Route::post('/', function (Foo $foo) {
    return $foo;
});

Route::post('/controller', 'App\Http\Controllers\FooBarController@index');
