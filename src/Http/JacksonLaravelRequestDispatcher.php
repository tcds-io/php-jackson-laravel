<?php

namespace Tcds\Io\Jackson\Laravel\Http;

use Illuminate\Container\Container;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tcds\Io\Generic\Reflection\ReflectionFunction;
use Tcds\Io\Generic\Reflection\ReflectionFunctionParameter;
use Tcds\Io\Generic\Reflection\ReflectionMethod;
use Tcds\Io\Generic\Reflection\ReflectionMethodParameter;
use Tcds\Io\Generic\Reflection\Type\ReflectionType;
use Tcds\Io\Jackson\Exception\JacksonException;
use Tcds\Io\Jackson\Exception\UnableToParseValue;
use Tcds\Io\Jackson\Laravel\Http\Dispatchers\JacksonLaravelResponseWrapper;
use Tcds\Io\Jackson\Laravel\JacksonConfig;
use Tcds\Io\Jackson\ObjectMapper;
use Throwable;

class JacksonLaravelRequestDispatcher
{
    /** @var array<string, mixed>|null */
    private ?array $cache = null;

    /** @var array<string, mixed> */
    private array $data {
        get => $this->cache ??= array_merge(
            $this->config->getCustomParams(container: $this->container, mapper: $this->mapper),
            $this->request->query->all(),
            $this->request->request->all(),
            $this->request->route()->parameters,
        );
    }

    public function __construct(
        private readonly ObjectMapper $mapper,
        private readonly Container $container,
        private readonly JacksonLaravelResponseWrapper $wrapper,
        private readonly Request $request,
        private readonly JacksonConfig $config,
    ) {
    }

    public function dispatch(ReflectionMethod|ReflectionFunction $function, callable $callable): mixed
    {
        $returnType = $function->getReturnType()->getName();
        $params = $function->getParameters();
        $resolved = $this->resolveParams($params);

        $response = call_user_func($callable, ...$resolved);

        return $this->wrapper->respond($response, $returnType);
    }

    private function resolveParams(array $params): array
    {
        return collect($params)
            ->mapWithKeys($this->resolveParamValue(...))
            ->filter(fn($value) => !is_null($value))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function resolveParamValue(ReflectionFunctionParameter|ReflectionMethodParameter $param): array
    {
        if ($param->isVariadic()) {
            return $this->request->route()->parameters;
        }

        $name = $param->name;
        $type = $param->getType()->getName();

        $value = match (true) {
            $this->config->readable($type) => $this->parseSerializableType($type),
            array_key_exists($name, $this->data) => $this->data[$name],
            default => $this->make($type, $name),
        };

        return [$name => $value];
    }

    /**
     * @template T of object
     * @param class-string<T> $type
     * @return T
     * @throws JacksonException
     */
    private function parseSerializableType(string $type): mixed
    {
        $isList = ReflectionType::isList($type);

        try {
            return $this->mapper->readValue(
                type: $type,
                value: $this->getRequestData($isList),
            );
        } catch (UnableToParseValue $e) {
            throw new HttpResponseException(
                new JsonResponse([
                    'message' => $e->getMessage(),
                    'expected' => $e->expected,
                    'given' => $e->given,
                ], Response::HTTP_BAD_REQUEST),
            );
        }
    }

    /**
     * @return array<string, mixed>|list<mixed>
     */
    private function getRequestData(bool $isList): array
    {
        return $isList
            // when desired type is list, then grab only payload because
            // query and path params will mess up with the list payload
            ? $this->request->request->all()
            // return the whole request merged into a single array
            : $this->data;
    }

    private function make(string $type, string $name): mixed
    {
        try {
            return $this->container->make($type);
        } catch (Throwable $e) {
            throw new JacksonException("Cannot resolve `$type \$$name` from request", previous: $e);
        }
    }
}
