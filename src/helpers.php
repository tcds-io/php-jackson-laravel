<?php

namespace Tcds\Io\Jackson\Laravel;

use Tcds\Io\Jackson\Laravel\Http\JacksonResponse;

if (!function_exists(__NAMESPACE__ . '\\jackson')) {
    function jackson(mixed $serializable): JacksonResponse
    {
        return new JacksonResponse($serializable);
    }
}
