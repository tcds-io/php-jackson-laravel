<?php

namespace Tcds\Io\Laravel\Jackson\Http\Dispatchers;

use Illuminate\Http\JsonResponse;
use Tcds\Io\Jackson\ObjectMapper;
use Tcds\Io\Laravel\Jackson\Http\JacksonResponse;

readonly class JacksonResponseWrapper
{
    public function __construct(private ObjectMapper $mapper)
    {
    }

    public function respond($response)
    {
        if ($response instanceof JacksonResponse) {
            return new JsonResponse(
                data: $this->mapper->writeValue($response->serializable),
                status: $response->getStatusCode(),
                headers: $response->headers?->all() ?: [],
            );
        }

        return $this->isSerializable($response)
            ? new JsonResponse($this->mapper->writeValue($response))
            : $response;
    }

    private function isSerializable($response): bool
    {
        $classes = config('serializer.classes', []);

        /**
         * Laravel does not allow traversable responses, so we can assume it should be serialized
         */
        if (is_iterable($response)) {
            return true;
        }

        /**
         * if it is not an object then it is not something we can serialize
         */
        if (false === is_object($response)) {
            return false;
        }

        return array_key_exists(get_class($response), $classes);
    }
}
