<?php

namespace Tcds\Io\Jackson\Laravel\Http\Dispatchers;

use Illuminate\Container\Container;
use Illuminate\Routing\CallableDispatcher;
use Illuminate\Routing\Route;
use ReflectionFunction;
use ReflectionParameter;
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
        collect(new ReflectionFunction($callable)
            ->getParameters())
            ->each(fn(ReflectionParameter $parameter) => $this->resolver->resolve(
                $parameter->getName(),
                $parameter->getType()->getName(),
                $route,
            ));

        $response = parent::dispatch($route, $callable);

        return $this->wrapper->respond($response);
    }
}
