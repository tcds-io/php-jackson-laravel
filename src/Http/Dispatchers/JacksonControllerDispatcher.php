<?php

namespace Tcds\Io\Laravel\Jackson\Http\Dispatchers;

use Illuminate\Container\Container;
use Illuminate\Routing\ControllerDispatcher;
use Illuminate\Routing\Route;
use Throwable;

class JacksonControllerDispatcher extends ControllerDispatcher
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

    public function dispatch(Route $route, $controller, $method)
    {
        $response = parent::dispatch($route, $controller, $method);

        return $this->wrapper->respond($response);
    }
}
