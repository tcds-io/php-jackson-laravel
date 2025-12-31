<?php

namespace Tcds\Io\Jackson\Laravel\Http\Dispatchers;

use Symfony\Component\HttpFoundation\Response;
use Tcds\Io\Generic\Reflection\Type\Parser\TypeParser;
use Tcds\Io\Generic\Reflection\Type\ReflectionType;
use Tcds\Io\Jackson\Exception\JacksonException;
use Tcds\Io\Jackson\Laravel\Http\JacksonLaravelResponse;
use Tcds\Io\Jackson\ObjectMapper;

readonly class JacksonLaravelResponseWrapper
{
    public function __construct(private ObjectMapper $mapper, private array $classes)
    {
    }

    public function respond(mixed $response, string $returnType): mixed
    {
        return match (true) {
            $returnType === 'void' => null,
            $response instanceof Response => $response,
            $response instanceof JacksonLaravelResponse => $response->toJsonResponse($this->mapper),
            $this->isSerializable($response, $returnType) => $this->mapper->writeValue($response),
            default => throw new JacksonException("Missing serializer for endpoint response type <$returnType>."),
        };
    }

    private function isSerializable(mixed $response, string $returnType): bool
    {
        [$type, $generics] = TypeParser::getGenericTypes($returnType);
        $type = $type === 'mixed' && is_object($response) ? $response::class : $type;
        $isList = ReflectionType::isList($type);
        $listType = $isList ? $generics[0] ?? 'mixed' : 'mixed';

        return isset($this->classes[$type]) || ($isList && isset($this->classes[$listType]));
    }
}
