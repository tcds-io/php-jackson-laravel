<?php

use Psr\Container\ContainerInterface;
use Tcds\Io\Jackson\ObjectMapper;

/**
 * @returns array{
 *     mappers: array<class-string, array{
 *         reader?: callable(mixed $value, string $type, ObjectMapper $mapper, array $path): mixed,
 *         writer?: callable(mixed $data, string $type, ObjectMapper $mapper, array $path): mixed,
 *     }>,
 *     params?: callable(ContainerInterface $container, ObjectMapper $mapper): array
 * }
 */
return [
    'mappers' => [],
    'params' => fn () => [],
];
