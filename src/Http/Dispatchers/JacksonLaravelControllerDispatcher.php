<?php

namespace Tcds\Io\Jackson\Laravel\Http\Dispatchers;

use Illuminate\Routing\ControllerDispatcher;
use Illuminate\Routing\Route;
use Override;
use Tcds\Io\Generic\Reflection\ReflectionClass;
use Tcds\Io\Jackson\Laravel\Http\JacksonLaravelRequestDispatcher;

class JacksonLaravelControllerDispatcher extends ControllerDispatcher
{
    /**
     * @param object $controller
     * @param string $method
     */
    #[Override]
    public function dispatch(Route $route, $controller, $method)
    {
        // resolved per dispatch: this class is a singleton, but the request dispatcher
        // holds the current Request and merged request data, which must not outlive
        // the request under long-running runtimes like Octane
        $dispatcher = $this->container->make(JacksonLaravelRequestDispatcher::class);

        $function = new ReflectionClass($controller::class)->getMethod($method);
        $parameters = $this->resolveClassMethodDependencies(
            $dispatcher->seedParameters($function, $route->parametersWithoutNulls()),
            $controller,
            $method,
        );

        return $dispatcher->dispatch($function, $controller->$method(...), $parameters);
    }
}
