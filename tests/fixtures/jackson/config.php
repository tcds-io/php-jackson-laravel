<?php

declare(strict_types=1);

use App\Services\AuthTokenService;
use Psr\Container\ContainerInterface;
use Tcds\Io\Jackson\Node\Reader;
use Tcds\Io\Jackson\Node\StaticReader;
use Tcds\Io\Jackson\Node\StaticWriter;
use Tcds\Io\Jackson\Node\Writer;
use Tcds\Io\Jackson\ObjectMapper;

/**
 * @returns array{
 *     mappers: array<class-string, array{
 *         reader?: Reader<mixed>|StaticReader<mixed>|Closure(mixed $data, string $type, ObjectMapper $mapper, list<string> $path): mixed,
 *         writer?: Writer<mixed>|StaticWriter<mixed>|Closure(mixed $data, string $type, ObjectMapper $mapper, list<string> $path): mixed,
 *     }>,
 *     params?: callable(ContainerInterface $container, ObjectMapper $mapper): array
 * }
 */
return [
    'mappers' => [
        App\Models\Foo::class => [],
        App\Models\UserSettings::class => [],
        App\Queries\InvoiceQuery::class => [],
    ],
    'params' => function () {
        $authService = app(AuthTokenService::class);

        return $authService->getClaims();
    },
];
