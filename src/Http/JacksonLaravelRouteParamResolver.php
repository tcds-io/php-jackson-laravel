<?php

namespace Tcds\Io\Jackson\Laravel\Http;

use Illuminate\Config\Repository as Config;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Tcds\Io\Generic\Reflection\Type\Parser\TypeParser;
use Tcds\Io\Generic\Reflection\Type\ReflectionType;
use Tcds\Io\Jackson\Exception\JacksonException;
use Tcds\Io\Jackson\Exception\UnableToParseValue;
use Tcds\Io\Jackson\ObjectMapper;

readonly class JacksonLaravelRouteParamResolver
{
    /** @var array<string, array> */
    private array $config;

    public function __construct(
        private ObjectMapper $mapper,
        private Request $request,
        Config $config,
    ) {
        $this->config = $config->get('serializer.classes', []);
    }

    public function resolve(string $name, string $type, Route $route): void
    {
        [$main, $generics] = TypeParser::getGenericTypes($type);
        $listType = $generics[0] ?? 'mixed';

        $serializableType = match (true) {
            isset($this->config[$main]) => $main,
            ReflectionType::isList($main) && isset($this->config[$listType]) => $type,
            // shape
            default => null,
        };

        if (is_null($serializableType)) {
            return;
        }

        $value = $this->parseType($serializableType);
        $route->setParameter($name, $value);
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function parseType(string $class): mixed
    {
        try {
            return $this->decodeRequest($class);
        } catch (UnableToParseValue $e) {
            throw new HttpResponseException(
                new JsonResponse([
                    'message' => $e->getMessage(),
                    'expected' => $e->expected,
                    'given' => $e->given,
                ], Response::HTTP_BAD_REQUEST),
            );
        } catch (JacksonException $e) {
            throw $this->createBadRequest($e->getMessage());
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws UnableToParseValue
     * @throws JacksonException
     */
    private function decodeRequest(string $class)
    {
        $data = array_merge(
            $this->request->query->all(),
            $this->request->request->all(),
            $this->request->route()->parameters,
        );

        return $this->mapper->readValue($class, $data);
    }

    private function createBadRequest(string $message): HttpResponseException
    {
        return new HttpResponseException(
            response: new JsonResponse(['message' => $message], Response::HTTP_BAD_REQUEST),
        );
    }
}
