<?php

namespace Tcds\Io\Jackson\Laravel\Http;

use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Tcds\Io\Generic\Reflection\ReflectionFunction;
use Tcds\Io\Generic\Reflection\ReflectionFunctionParameter;
use Tcds\Io\Generic\Reflection\ReflectionMethod;
use Tcds\Io\Generic\Reflection\ReflectionMethodParameter;
use Tcds\Io\Generic\Reflection\Type\ReflectionType;
use Tcds\Io\Jackson\Exception\JacksonException;
use Tcds\Io\Jackson\Exception\UnableToParseValue;
use Tcds\Io\Jackson\Laravel\Attributes\Inject;
use Tcds\Io\Jackson\Laravel\Attributes\JacksonResponse;
use Tcds\Io\Jackson\Laravel\Http\Dispatchers\JacksonLaravelResponseWrapper;
use Tcds\Io\Jackson\Laravel\JacksonConfig;
use Tcds\Io\Jackson\Laravel\JacksonLaravelException;
use Tcds\Io\Jackson\ObjectMapper;
use Throwable;

class JacksonLaravelRequestDispatcher
{
    /** @var array<string, mixed>|null */
    private ?array $cache = null;

    /** @var array<string, mixed> */
    private array $data {
        get {
            if ($this->cache !== null) {
                return $this->cache;
            }

            /** @var array<string, mixed> $routeParams */
            $routeParams = $this->request->route()->parameters ?? [];

            return $this->cache = array_merge(
                $this->config->getCustomParams(container: $this->container, mapper: $this->mapper),
                $this->request->query->all(),
                $this->request->request->all(),
                $this->objectBodyData(),
                $routeParams,
            );
        }
    }

    public function __construct(
        private readonly ObjectMapper $mapper,
        private readonly Container $container,
        private readonly JacksonLaravelResponseWrapper $wrapper,
        private readonly Request $request,
        private readonly JacksonConfig $config,
    ) {}

    public function dispatch(ReflectionMethod|ReflectionFunction $function, callable $callable): mixed
    {
        $returnType = $function->getReturnType()->getName();
        $params = $function->getParameters();
        $resolved = $this->resolveParams($params);
        $jacksonResponse = ($function->getAttributes(JacksonResponse::class)[0] ?? null)?->newInstance();

        $response = call_user_func($callable, ...$resolved);

        return $this->wrapper->respond($response, $returnType, $jacksonResponse);
    }

    /**
     * @param list<ReflectionFunctionParameter|ReflectionMethodParameter> $params
     * @return array<string, mixed>
     */
    private function resolveParams(array $params): array
    {
        $resolved = [];
        foreach ($params as $param) {
            $resolved += $this->resolveParamValue($param);
        }

        return $resolved;
    }

    /**
     * @return array<string, mixed>
     */
    public function resolveParamValue(ReflectionFunctionParameter|ReflectionMethodParameter $param): array
    {
        if ($param->isVariadic()) {
            /** @var array<string, mixed> $routeParams */
            $routeParams = $this->request->route()->parameters ?? [];

            return [$param->name => $routeParams];
        }

        $name = $param->name;
        $type = $param->getType()->getName();
        $hasInject = $param->getAttributes(Inject::class) !== [];

        $value = match (true) {
            $hasInject => $this->parseSerializableType($name, $type),
            $this->config->readable($type) => $this->parseSerializableType($name, $type),
            array_key_exists($name, $this->data) => $this->data[$name],
            default => $this->make($type, $name),
        };

        return [$name => $value];
    }

    /**
     * @throws JacksonException
     */
    private function parseSerializableType(string $name, string $type): mixed
    {
        try {
            $isList = ReflectionType::isList($type);
            $data = $this->getRequestData($isList);

            if (ReflectionType::isEnum($type) && ($data[$name] ?? null) instanceof $type) {
                return $data[$name];
            }

            return $this->mapper->readValue(type: generic($type, []), value: $data);
        } catch (UnableToParseValue $e) {
            throw $this->config->handleRequestError($e);
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
            ? $this->bodyData()
            // return the whole request merged into a single array
            : $this->data;
    }

    /**
     * @return array<string, mixed>|list<mixed>
     */
    private function bodyData(): array
    {
        /** @var array<string, mixed>|list<mixed> */
        return $this->request->isJson()
            ? $this->request->json()->all()
            : $this->request->request->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function objectBodyData(): array
    {
        $data = $this->bodyData();

        if (array_is_list($data)) {
            return [];
        }

        $objectData = [];
        foreach ($data as $key => $value) {
            if (is_string($key)) {
                $objectData[$key] = $value;
            }
        }

        return $objectData;
    }

    private function make(string $type, string $name): mixed
    {
        try {
            return $this->container->make($type);
        } catch (Throwable $e) {
            throw new JacksonLaravelException("Cannot resolve `$type \$$name` from request", previous: $e);
        }
    }
}
