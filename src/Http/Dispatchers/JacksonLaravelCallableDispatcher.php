<?php

namespace Tcds\Io\Jackson\Laravel\Http\Dispatchers;

use Illuminate\Container\Container;
use Illuminate\Routing\CallableDispatcher;
use Illuminate\Routing\Route;
use ReflectionFunction;
use ReflectionParameter;
use Tcds\Io\Generic\Reflection\Type\Parser\OriginalTypeParser;
use Tcds\Io\Jackson\Laravel\Http\JacksonLaravelRouteParamResolver;
use Throwable;

class JacksonLaravelCallableDispatcher extends CallableDispatcher
{
    private JacksonLaravelResponseWrapper $wrapper;

    /**
     * @throws Throwable
     */
    public function __construct(Container $container, private readonly JacksonLaravelRouteParamResolver $resolver)
    {
        parent::__construct($container);

        $this->wrapper = $container->get(JacksonLaravelResponseWrapper::class);
    }

    public function dispatch(Route $route, $callable)
    {
        $function = new ReflectionFunction($callable);

        /**
         * TODO: replace by better-generics reflection
         */
        collect($function->getParameters())
            ->each(fn(ReflectionParameter $parameter) => $this->resolver->resolve(
                $parameter->getName(),
                $parameter->getType()->getName(),
                $route,
            ));

        $response = parent::dispatch($route, $callable);

        /**
         * TODO: replace by better-generics type guessing
         */
        $returnType = match (true) {
            is_object($response) => $response::class,
            default => OriginalTypeParser::parse($function->getReturnType()),
        };

        return $this->wrapper->respond($response, $returnType);
    }
}
