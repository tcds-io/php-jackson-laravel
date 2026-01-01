<?php

declare(strict_types=1);

use App\Services\AuthTokenService;
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
    'classes' => [
        App\Models\Foo::class => [],
        App\Queries\InvoiceQuery::class => [],
    ],
    'params' => function () {
        $authService = app(AuthTokenService::class);

        return $authService->getClaims();
    },
];
