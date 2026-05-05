<?php

namespace Tcds\Io\Jackson\Laravel\Http;

use Illuminate\Http\JsonResponse;
use Tcds\Io\Jackson\ObjectMapper;

class JacksonResponse
{
    /**
     * @param array<string, string|list<string>> $headers
     */
    public function __construct(
        private readonly mixed $serializable,
        private int $status = 200,
        private array $headers = [],
    ) {}

    public function status(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @param string|list<string> $value
     */
    public function header(string $name, string|array $value): self
    {
        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * @param array<string, string|list<string>> $headers
     */
    public function headers(array $headers): self
    {
        $this->headers = [...$this->headers, ...$headers];

        return $this;
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
