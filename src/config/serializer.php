<?php

use Tcds\Io\Jackson\ObjectMapper;

/**
 * @returns array{
 *     classes: array<class-string, array{
 *         reader?: callable(mixed $value, string $type, ObjectMapper $mapper, array $path): mixed,
 *         writer?: callable(mixed $data, string $type, ObjectMapper $mapper, array $path): mixed,
 *     }>,
 *     params?: callable(ObjectMapper $mapper): array
 * }
 */
return [
    'classes' => [],
    'params' => fn() => [],
];
