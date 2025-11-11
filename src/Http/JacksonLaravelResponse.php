<?php

namespace Tcds\Io\Jackson\Laravel\Http;

use Illuminate\Http\JsonResponse;

class JacksonLaravelResponse extends JsonResponse
{
    public function __construct(
        public readonly mixed $serializable,
        $status = 200,
        $headers = [],
    ) {
        parent::__construct($serializable, $status, $headers);
    }
}
