<?php

namespace Tcds\Io\Jackson\Laravel\Http;

use Illuminate\Http\JsonResponse;
use Tcds\Io\Jackson\ObjectMapper;

readonly class JacksonLaravelResponse
{
    public function __construct(
        private mixed $serializable,
        private int $status = 200,
        private array $headers = [],
    ) {
    }

    public function toJsonResponse(ObjectMapper $mapper): JsonResponse
    {
        return new JsonResponse(
            data: $mapper->writeValue($this->serializable),
            status: $this->status,
            headers: $this->headers,
        );
    }
}
