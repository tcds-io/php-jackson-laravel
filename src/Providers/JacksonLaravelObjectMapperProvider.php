<?php

namespace Tcds\Io\Jackson\Laravel\Providers;

use Carbon\Laravel\ServiceProvider;
use Illuminate\Routing\Contracts\CallableDispatcher;
use Illuminate\Routing\Contracts\ControllerDispatcher;
use Tcds\Io\Jackson\ArrayObjectMapper;
use Tcds\Io\Jackson\JsonObjectMapper;
use Tcds\Io\Jackson\Laravel\Http\Dispatchers\JacksonLaravelCallableDispatcher;
use Tcds\Io\Jackson\Laravel\Http\Dispatchers\JacksonLaravelControllerDispatcher;
use Tcds\Io\Jackson\Laravel\Http\JacksonLaravelRequestDispatcher;
use Tcds\Io\Jackson\ObjectMapper;

class JacksonLaravelObjectMapperProvider extends ServiceProvider
{
    public function boot(): void
    {
        $config = __DIR__ . '/../../config/serializer.php';
        $this->publishes([$config => config_path('serializer.php')]);
    }

    public function register(): void
    {
        $classes = config('serializer.classes', []);
        $arrayMapper = new ArrayObjectMapper(typeMappers: $classes);

        $this->app->singleton(ArrayObjectMapper::class, fn() => $arrayMapper);
        $this->app->singleton(JsonObjectMapper::class, fn() => new JsonObjectMapper(typeMappers: $classes));
        $this->app->singleton(ObjectMapper::class, fn() => $arrayMapper);
        $this->app->singleton(CallableDispatcher::class, JacksonLaravelCallableDispatcher::class);
        $this->app->singleton(ControllerDispatcher::class, JacksonLaravelControllerDispatcher::class);
        $this->app->singleton(JacksonLaravelRequestDispatcher::class, JacksonLaravelRequestDispatcher::class);
    }
}
