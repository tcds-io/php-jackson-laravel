<?php

namespace App\Http\Controllers;

use App\Models\Foo;

class FooBarController
{
    public function index(Foo $foo): Foo
    {
        return $foo;
    }
}
