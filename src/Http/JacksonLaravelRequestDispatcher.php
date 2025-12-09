<?php

namespace Tcds\Io\Jackson\Laravel\Http;

use Illuminate\Config\Repository as Config;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Psr\Container\ContainerInterface;
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
    private array $config;

    /** @var array<string, mixed> */
    private array $data;

    public function __construct(
        private ObjectMapper $mapper,
        private ContainerInterface $container,
        private JacksonLaravelResponseWrapper $wrapper,
        private Request $request,
        Config $config,
    ) {
        $this->config = $config->get('serializer.classes', []);
        $customParams = $config->get('serializer.params') ?? fn(ObjectMapper $mapper) => [];

        $this->data = array_merge(
            $customParams($this->mapper),
            $this->request->query->all(),
            $this->request->request->all(),
            $this->request->route()->parameters,
        );
    }

    public function dispatch(ReflectionMethod|ReflectionFunction $function, callable $callable): mixed
    {
        $returnType = $function->getReturnType()->getName();
        $params = $this->resolveParams($function->getParameters());

        $response = call_user_func($callable, ...$params);

        return $this->wrapper->respond($response, $returnType);
    }

    private function resolveParams(array $params): array
    {
        return collect($params)
            ->mapWithKeys(fn(ReflectionFunctionParameter|ReflectionMethodParameter $param) => [
                $param->name => $param->getType()->getName(),
            ])
            ->map(fn(string $type, string $name) => $this->resolveParamByNameAndType($name, $type))
            ->filter(fn($value) => !is_null($value))
            ->toArray();
    }

    public function resolveParamByNameAndType(string $name, string $type): mixed
    {
        [$main, $generics] = TypeParser::getGenericTypes($type);
        $isList = ReflectionType::isList($main);

        if ($isList) {
            $main = $generics[0] ?? 'mixed';
        }

        return match (true) {
            $this->isSerializable($main) => $this->parseSerializableType($type, $isList),
            $this->container->has($type) => $this->container->get($type),
            array_key_exists($name, $this->data) => $this->data[$name],
            default => throw new JacksonException("Cannot resolve `$name` from request"),
        };
    }

    private function isSerializable(string $type): bool
    {
        $config = $this->config[$type] ?? null;

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
