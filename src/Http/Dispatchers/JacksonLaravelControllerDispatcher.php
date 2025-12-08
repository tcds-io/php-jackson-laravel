<?php

namespace Tcds\Io\Jackson\Laravel\Http\Dispatchers;

use Illuminate\Container\Container;
use Illuminate\Routing\ControllerDispatcher;
use Illuminate\Routing\Route;
use Override;
use Tcds\Io\Generic\Reflection\ReflectionClass;
use Tcds\Io\Jackson\Laravel\Http\JacksonLaravelRequestDispatcher;

class JacksonLaravelControllerDispatcher extends ControllerDispatcher
{
    public function __construct(Container $container, private readonly JacksonLaravelRequestDispatcher $dispatcher)
    {
        parent::__construct($container);
    }

    #[Override]
    public function dispatch(Route $route, $controller, $method)
    {
        $function = new ReflectionClass($controller::class)->getMethod($method);

        return $this->dispatcher->dispatch($function, [$controller, $method]);
    }
}
