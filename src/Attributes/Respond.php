<?php

namespace Tcds\Io\Jackson\Laravel\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION)]
final readonly class Respond
{
    public function __construct(public int $statusCode = 200) {}
}
