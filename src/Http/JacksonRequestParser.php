<?php

namespace Tcds\Io\Laravel\Jackson\Http;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Tcds\Io\Jackson\Exception\JacksonException;
use Tcds\Io\Jackson\Exception\UnableToParseValue;
use Tcds\Io\Jackson\ObjectMapper;

readonly class JacksonRequestParser
{
    public function __construct(private ObjectMapper $mapper, private Request $request)
    {
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function parseRequest(string $class): object
    {
        try {
            return $this->decodeRequest($class);
        } catch (UnableToParseValue $e) {
            throw new HttpResponseException(
                new JsonResponse([
                    'message' => $e->getMessage(),
                    'expected' => $e->expected,
                    'given' => $e->given,
                ], Response::HTTP_BAD_REQUEST),
            );
        } catch (JacksonException $e) {
            throw $this->createBadRequest($e->getMessage());
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws UnableToParseValue
     * @throws JacksonException
     */
    private function decodeRequest(string $class)
    {
        $data = array_merge(
            $this->request->query->all(),
            $this->request->request->all(),
            $this->request->route()->parameters,
        );

        return $this->mapper->readValue($class, $data);
    }

    private function createBadRequest(string $message): HttpResponseException
    {
        return new HttpResponseException(
            response: new JsonResponse(['message' => $message], Response::HTTP_BAD_REQUEST),
        );
    }
}
