<?php

namespace Tcds\Io\Jackson\Laravel\Http\Dispatchers;

use Closure;
use Illuminate\Routing\CallableDispatcher;
use Illuminate\Routing\Route;
use Override;
use Tcds\Io\Generic\Reflection\ReflectionFunction;
use Tcds\Io\Jackson\Laravel\Http\JacksonLaravelRequestDispatcher;

class JacksonLaravelCallableDispatcher extends CallableDispatcher
{
    #[Override]
    public function dispatch(Route $route, $callable)
    {
        // resolved per dispatch: this class is a singleton, but the request dispatcher
        // holds the current Request and merged request data, which must not outlive
        // the request under long-running runtimes like Octane
        $dispatcher = $this->container->make(JacksonLaravelRequestDispatcher::class);

        $closure = Closure::fromCallable($callable);
        $function = new ReflectionFunction($closure);
        $parameters = $this->resolveMethodDependencies(
            $dispatcher->seedParameters($function, $route->parametersWithoutNulls()),
            new \ReflectionFunction($closure),
        );

        return $dispatcher->dispatch($function, $closure, $parameters);
    }
}
