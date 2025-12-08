<?php

namespace Tcds\Io\Jackson\Laravel\Http\Dispatchers;

use Illuminate\Container\Container;
use Illuminate\Routing\CallableDispatcher;
use Illuminate\Routing\Route;
use Override;
use Tcds\Io\Generic\Reflection\ReflectionFunction;
use Tcds\Io\Jackson\Laravel\Http\JacksonLaravelRequestDispatcher;
use Throwable;

class JacksonLaravelCallableDispatcher extends CallableDispatcher
{
    /**
     * @throws Throwable
     */
    public function __construct(Container $container, private readonly JacksonLaravelRequestDispatcher $dispatcher)
    {
        parent::__construct($container);
    }

    #[Override]
    public function dispatch(Route $route, $callable)
    {
        $function = new ReflectionFunction($callable);

        return $this->dispatcher->dispatch($function, $callable);
    }
}
