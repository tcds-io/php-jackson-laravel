<?php

namespace Tcds\Io\Jackson\Laravel\Http\Dispatchers;

use Illuminate\Container\Container;
use Illuminate\Routing\ControllerDispatcher;
use Illuminate\Routing\Route;
use Tcds\Io\Generic\Reflection\ReflectionClass;
use Tcds\Io\Generic\Reflection\ReflectionParameter;
use Tcds\Io\Jackson\Laravel\Http\JacksonLaravelRouteParamResolver;
use Throwable;

class JacksonLaravelControllerDispatcher extends ControllerDispatcher
{
    private JacksonLaravelResponseWrapper $wrapper;

    /**
     * @throws Throwable
     */
    public function __construct(Container $container, private readonly JacksonLaravelRouteParamResolver $parser)
    {
        parent::__construct($container);

        $this->wrapper = $container->get(JacksonLaravelResponseWrapper::class);
    }

    public function dispatch(Route $route, $controller, $method)
    {
        $function = new ReflectionClass($controller::class)->getMethod($method);
        $returnType = $function->getReturnType()->getName();

        collect($function->getParameters())
            ->each(fn(ReflectionParameter $parameter) => $this->parser->resolve(
                $parameter->getName(),
                $parameter->getType()->getName(),
                $route,
            ));

        $response = parent::dispatch($route, $controller, $method);

        return $this->wrapper->respond($response, $returnType);
    }
}
