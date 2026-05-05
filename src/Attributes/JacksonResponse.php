<?php

namespace Tcds\Io\Jackson\Laravel\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION)]
final readonly class JacksonResponse
{
    /**
     * @param array<string, string|list<string>> $headers
     */
    public function __construct(
        public int $status = 200,
        public array $headers = [],
    ) {}
}
