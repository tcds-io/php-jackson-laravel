<?php

namespace Tcds\Io\Jackson\Laravel\Http\Dispatchers;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Routing\CallableDispatcher;
use Illuminate\Routing\Route;
use Override;
use Tcds\Io\Generic\Reflection\ReflectionFunction;
use Tcds\Io\Jackson\Laravel\Http\JacksonLaravelRequestDispatcher;

class JacksonLaravelCallableDispatcher extends CallableDispatcher
{
    public function __construct(Container $container, private readonly JacksonLaravelRequestDispatcher $dispatcher)
    {
        parent::__construct($container);
    }

    #[Override]
    public function dispatch(Route $route, $callable)
    {
        $closure = Closure::fromCallable($callable);
        $function = new ReflectionFunction($closure);
        $parameters = $this->resolveMethodDependencies(
            $this->dispatcher->seedParameters($function, $route->parametersWithoutNulls()),
            new \ReflectionFunction($closure),
        );

        return $this->dispatcher->dispatch($function, $closure, $parameters);
    }
}
