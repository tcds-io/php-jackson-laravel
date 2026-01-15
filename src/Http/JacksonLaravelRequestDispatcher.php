<?php

namespace Tcds\Io\Jackson\Laravel\Http;

use Illuminate\Config\Repository as Config;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Psr\Container\ContainerInterface as Container;
use Symfony\Component\HttpFoundation\Response;
use Tcds\Io\Generic\Reflection\ReflectionFunction;
use Tcds\Io\Generic\Reflection\ReflectionFunctionParameter;
use Tcds\Io\Generic\Reflection\ReflectionMethod;
use Tcds\Io\Generic\Reflection\ReflectionMethodParameter;
use Tcds\Io\Generic\Reflection\Type\Parser\TypeParser;
use Tcds\Io\Generic\Reflection\Type\ReflectionType;
use Tcds\Io\Jackson\Exception\JacksonException;
use Tcds\Io\Jackson\Exception\UnableToParseValue;
use Tcds\Io\Jackson\Laravel\Http\Dispatchers\JacksonLaravelResponseWrapper;
use Tcds\Io\Jackson\ObjectMapper;

readonly class JacksonLaravelRequestDispatcher
{
    /** @var array<string, array{ reader?: mixed }> */
    private array $mappers;

    /** @var array<string, mixed> */
    private array $data;

    public function __construct(
        private ObjectMapper $mapper,
        private Container $container,
        private JacksonLaravelResponseWrapper $wrapper,
        private Request $request,
        Config $config,
    ) {
        $this->mappers = $config->get('jackson.mappers', []);
        $customParams = $config->get('jackson.params') ?? fn (Container $container, ObjectMapper $mapper) => [];

        $this->data = array_merge(
            ReflectionFunction::call($customParams, ['container' => $container, 'mapper' => $this->mapper]),
            $this->request->query->all(),
            $this->request->request->all(),
            $this->request->route()->parameters,
        );
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
            ->filter(fn ($value) => !is_null($value))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function resolveParamValue(ReflectionFunctionParameter|ReflectionMethodParameter $param): array
    {
        $name = $param->name;
        $type = $param->getType()->getName();
        [$main, $generics] = TypeParser::getGenericTypes($type);
        $isList = ReflectionType::isList($main);

        if ($isList) {
            $main = $generics[0] ?? 'mixed';
        }

        if ($param->isVariadic()) {
            return $this->request->route()->parameters;
        }

        $value = match (true) {
            $this->isSerializable($main) => $this->parseSerializableType($type, $isList),
            $this->container->has($type) => $this->container->get($type),
            array_key_exists($name, $this->data) => $this->data[$name],
            default => throw new JacksonException("Cannot resolve `$type \$$name` from request"),
        };

        return [$name => $value];
    }

    private function isSerializable(string $type): bool
    {
        $config = $this->mappers[$type] ?? null;

        if (null === $config) {
            /**
             * the type was not configured to be read
             */
            return false;
        }

        if (array_key_exists('reader', $config) && $config['reader'] === null) {
            /**
             * the type was configured but the reader was set to null, meaning the type should not be read
             */
            return false;
        }

        return true;
    }

    /**
     * @template T of object
     * @param class-string<T> $type
     * @return T
     * @throws JacksonException
     */
    private function parseSerializableType(string $type, bool $isList): mixed
    {
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
}
