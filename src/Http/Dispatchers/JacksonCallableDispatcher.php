<?php

namespace Tcds\Io\Laravel\Jackson\Http\Dispatchers;

use Illuminate\Container\Container;
use Illuminate\Routing\CallableDispatcher;
use Illuminate\Routing\Route;
use Throwable;

class JacksonCallableDispatcher extends CallableDispatcher
{
    private JacksonResponseWrapper $wrapper;

    /**
     * @throws Throwable
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);

        $this->wrapper = $container->get(JacksonResponseWrapper::class);
    }

    public function dispatch(Route $route, $callable)
    {
        $response = parent::dispatch($route, $callable);

        return $this->wrapper->respond($response);
    }
}
