<?php

namespace Tcds\Io\Jackson\Laravel\Http\Dispatchers;

use Illuminate\Http\JsonResponse;
use Tcds\Io\Generic\Reflection\Type\Parser\TypeParser;
use Tcds\Io\Generic\Reflection\Type\ReflectionType;
use Tcds\Io\Jackson\Laravel\Http\JacksonLaravelResponse;
use Tcds\Io\Jackson\ObjectMapper;

readonly class JacksonLaravelResponseWrapper
{
    public function __construct(private ObjectMapper $mapper, private array $classes)
    {
    }

    public function respond(mixed $response, string $returnType): mixed
    {
        [$type, $generics] = TypeParser::getGenericTypes($returnType);
        $type = $type === 'mixed' && is_object($response) ? $response::class : $type;
        $isList = ReflectionType::isList($type);
        $listType = $isList ? $generics[0] ?? 'mixed' : 'mixed';

        return match (true) {
            $response instanceof JacksonLaravelResponse => new JsonResponse(
                data: $this->mapper->writeValue($response->serializable),
                status: $response->getStatusCode(),
                headers: $response->headers->all() ?: [],
            ),
            isset($this->classes[$type]),
            ($isList && isset($this->classes[$listType])) => $this->mapper->writeValue($response),
            default => $response,
        };
    }
}
