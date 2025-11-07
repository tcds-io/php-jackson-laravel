<?php

namespace Tcds\Io\Laravel\Jackson\Http;

use Illuminate\Http\JsonResponse;

class JacksonResponse extends JsonResponse
{
    public function __construct(
        public readonly mixed $serializable,
        $status = 200,
        $headers = [],
    ) {
        parent::__construct($serializable, $status, $headers);
    }
}
