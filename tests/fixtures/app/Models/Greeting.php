<?php

namespace App\Models;

readonly class Greeting
{
    public function __construct(public string $message) {}
}
