<?php

namespace Tcds\Io\Jackson\Laravel\Http\Dispatchers;

use Illuminate\Http\JsonResponse;
use Tcds\Io\Jackson\Laravel\Attributes\JacksonResponse as JacksonResponseAttribute;
use Tcds\Io\Jackson\Laravel\Http\JacksonResponse;
use Tcds\Io\Jackson\Laravel\JacksonConfig;
use Tcds\Io\Jackson\ObjectMapper;

readonly class JacksonLaravelResponseWrapper
{
    public function __construct(private ObjectMapper $mapper, private JacksonConfig $config) {}

    public function respond(mixed $response, string $returnType, ?JacksonResponseAttribute $jacksonResponse = null): mixed
    {
        return match (true) {
            $returnType === 'void' => null,
            $response instanceof JacksonResponse => $response->toJsonResponse($this->mapper),
            $this->config->writable($response, $returnType, $jacksonResponse) => new JsonResponse(
                data: $this->mapper->writeValue($response),
                status: $jacksonResponse->status ?? 200,
                headers: $jacksonResponse->headers ?? ['Content-Type' => 'application/json'],
            ),
            default => $response,
        };
    }
}
