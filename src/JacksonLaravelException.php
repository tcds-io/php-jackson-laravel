<?php

namespace Tcds\Io\Jackson\Laravel;

use Tcds\Io\Jackson\Exception\JacksonException;
use Throwable;

class JacksonLaravelException extends JacksonException
{
    public function __construct(string $message, Throwable $previous)
    {
        parent::__construct($message, previous: $previous);
    }
}
