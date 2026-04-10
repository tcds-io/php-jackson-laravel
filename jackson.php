<?php

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Tcds\Io\Jackson\Exception\UnableToParseValue;
use Tcds\Io\Jackson\Node\Reader;
use Tcds\Io\Jackson\Node\StaticReader;
use Tcds\Io\Jackson\Node\StaticWriter;
use Tcds\Io\Jackson\Node\Writer;
use Tcds\Io\Jackson\ObjectMapper;

/**
 * @returns array{
 *     errors?: array{
 *         request?: Closure(UnableToParseValue $e): Throwable
 *     },
 *     mappers: array<class-string, array{
 *         reader?: Reader<mixed>|StaticReader<mixed>|Closure(mixed $data, string $type, ObjectMapper $mapper, list<string> $path): mixed,
 *         writer?: Writer<mixed>|StaticWriter<mixed>|Closure(mixed $data, string $type, ObjectMapper $mapper, list<string> $path): mixed,
 *     }>,
 *     params?: callable(ContainerInterface $container, ObjectMapper $mapper): array
 * }
 */
return [
    'errors' => [
        // 'request' => fn(UnableToParseValue $e) => new HttpResponseException(
        //     new JsonResponse([
        //         'message' => $e->getMessage(),
        //         'expected' => $e->expected,
        //         'given' => $e->given,
        //     ], Response::HTTP_BAD_REQUEST),
        // ),
    ],
    'mappers' => [
        // 'class-string' => [
        //    'reader' => fn(mixed $data) => new class-string($data[...], $data[...]),
        //    'writer' => fn(class-string $data) => [...],
        //],
    ],
    'params' => fn() => [
        // 'userId' => Auth::user()->id
    ],
];
