<?php

namespace Tcds\Io\Jackson\Laravel\Mappers;

use Closure;
use Illuminate\Support\Collection;
use Override;
use Tcds\Io\Generic\Reflection\Type\Parser\GenericTypeParser;
use Tcds\Io\Jackson\Node\StaticReader;
use Tcds\Io\Jackson\Node\StaticWriter;
use Tcds\Io\Jackson\ObjectMapper;

/**
 * @phpstan-type MapperClosure Closure(mixed $data, string $type, ObjectMapper $mapper, list<string> $path): mixed
 * @template T of Collection
 * @implements StaticReader<T>
 * @implements StaticWriter<T>
 */
class CollectionMapper implements StaticReader, StaticWriter
{
    #[Override]
    public static function read(mixed $data, string $type, ObjectMapper $mapper, array $path): Collection
    {
        /** @var T $main */
        [$main, $generics] = GenericTypeParser::parse($type);
        $type = generic('list', $generics);
        $items = $mapper->readValue($type, $data) ?: [];

        return new $main($items);
    }

    #[Override]
    public static function write(mixed $data, string $type, ObjectMapper $mapper, array $path): mixed
    {
        return $mapper->writeValue($data?->all());
    }

    /**
     * @return array<string, array{ reader: MapperClosure, writer: MapperClosure }>
     */
    public static function get(string $type): array
    {
        /** @var array<string, array{ reader: MapperClosure, writer: MapperClosure }> */
        return [
            $type => [
                'reader' => static::read(...),
                'writer' => static::write(...),
            ],
        ];
    }
}
