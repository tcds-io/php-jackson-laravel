<?php

use Tcds\Io\Jackson\ObjectMapper;

/**
 * @returns array{
 *     classes: array<class-string, array{
 *         reader?: callable(mixed $value, string $type, ObjectMapper $mapper, array $trace): mixed,
 *         writer?: callable(mixed $data, string $type, ObjectMapper $mapper, array $trace): mixed,
 *     }>,
 *     params?: callable(ObjectMapper $mapper): array
 * }
 */
return [
    'classes' => [],
    'params' => fn() => [],
];
