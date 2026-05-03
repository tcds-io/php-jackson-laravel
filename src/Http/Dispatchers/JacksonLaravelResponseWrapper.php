<?php

namespace Tcds\Io\Jackson\Laravel\Http\Dispatchers;

use Illuminate\Http\JsonResponse;
use Tcds\Io\Jackson\Laravel\Attributes\Respond;
use Tcds\Io\Jackson\Laravel\Http\JacksonLaravelResponse;
use Tcds\Io\Jackson\Laravel\JacksonConfig;
use Tcds\Io\Jackson\ObjectMapper;

readonly class JacksonLaravelResponseWrapper
{
    public function __construct(private ObjectMapper $mapper, private JacksonConfig $config) {}

    public function respond(mixed $response, string $returnType, ?Respond $respond = null): mixed
    {
        return match (true) {
            $returnType === 'void' => null,
            $response instanceof JacksonLaravelResponse => $response->toJsonResponse($this->mapper),
            $respond !== null => new JsonResponse(
                data: $this->mapper->writeValue($response),
                status: $respond->statusCode,
            ),
            $this->config->writable($response, $returnType) => $this->mapper->writeValue($response),
            default => $response,
        };
    }
}
