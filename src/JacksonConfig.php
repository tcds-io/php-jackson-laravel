<?php

namespace Tcds\Io\Jackson\Laravel;

use Closure;
use Psr\Container\ContainerInterface as Container;
use Tcds\Io\Generic\Reflection\ReflectionFunction;
use Tcds\Io\Generic\Reflection\Type\Parser\TypeParser;
use Tcds\Io\Generic\Reflection\Type\ReflectionType;
use Tcds\Io\Jackson\ObjectMapper;

/**
 * @phpstan-import-type TypeMapper from ObjectMapper
 * @phpstan-type Mappers array<string, TypeMapper>
 * @phpstan-type CustomParams Closure(...$args): array<string, mixed>
 */
readonly class JacksonConfig
{
    /**
     * @param Mappers $mappers
     */
    public function __construct(public array $mappers, private Closure $customParams)
    {
    }

    public static function fromConfigFile(string $file): JacksonConfig
    {
        /** @var array{ mappers?: Mappers, params?: CustomParams } $config */
        $config = file_exists($file)
            ? require $file
            : [];

        $mappers = $config['mappers'] ?? [];
        $customParams = $config['params'] ?? fn() => [];

        return new self($mappers, $customParams);
    }

    public function readable(string $type): bool
    {
        [$main, $generics] = TypeParser::getGenericTypes($type);
        $isList = ReflectionType::isList($main);

        if ($isList) {
            $main = $generics[0] ?? 'mixed';
        }

        $config = $this->mappers[$main] ?? null;

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

    public function writable(mixed $value, string $returnType): bool
    {
        [$type, $generics] = TypeParser::getGenericTypes($returnType);
        $type = $type === 'mixed' && is_object($value) ? $value::class : $type;
        $isList = ReflectionType::isList($type);
        $listType = $isList ? $generics[0] ?? 'mixed' : 'mixed';

        return isset($this->mappers[$type]) || ($isList && isset($this->mappers[$listType]));
    }

    /**
     * @return array<string, mixed>
     */
    public function getCustomParams(Container $container, ObjectMapper $mapper): array
    {
        return ReflectionFunction::call($this->customParams, [
            'container' => $container,
            'mapper' => $mapper,
        ]);
    }
}
