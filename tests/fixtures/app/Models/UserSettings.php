<?php

namespace App\Models;

readonly class UserSettings
{
    public function __construct(public bool $drawer, public string $theme)
    {
    }
}
