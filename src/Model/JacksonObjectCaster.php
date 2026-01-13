<?php

namespace Tcds\Io\Jackson\Laravel\Model;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Override;
use Tcds\Io\Jackson\JsonObjectMapper;
use Tcds\Io\Jackson\ObjectMapper;

/**
 * @template T
 */
class JacksonObjectCaster implements CastsAttributes
{
    private ObjectMapper $mapper;

    /**
     * @param class-string<T> $class
     */
    public function __construct(
        private readonly string $class,
        ?ObjectMapper $mapper = null,
    ) {
        $this->mapper = $mapper ?? app(JsonObjectMapper::class);
    }

    /**
     * @param $model
     * @param string $key
     * @param $value
     * @return T|null
     */
    #[Override] public function get($model, string $key, $value, array $attributes): mixed
    {
        return $this->mapper->readValue($this->class, $value);
    }

    #[Override]
    public function set($model, string $key, $value, array $attributes): ?string
    {
        return is_a($value, $this->class)
            ? $this->mapper->writeValue($value)
            : $value;
    }
}
