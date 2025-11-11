<?php

namespace Tcds\Io\Jackson\Laravel\Providers;

use Carbon\Laravel\ServiceProvider;
use Illuminate\Routing\Contracts\CallableDispatcher;
use Illuminate\Routing\Contracts\ControllerDispatcher;
use Tcds\Io\Jackson\ArrayObjectMapper;
use Tcds\Io\Jackson\JsonObjectMapper;
use Tcds\Io\Jackson\ObjectMapper;
use Tcds\Io\Jackson\Laravel\Http\Dispatchers\JacksonLaravelCallableDispatcher;
use Tcds\Io\Jackson\Laravel\Http\Dispatchers\JacksonLaravelControllerDispatcher;
use Tcds\Io\Jackson\Laravel\Http\JacksonLaravelRouteParamResolver;

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

        $this->app->singleton(ArrayObjectMapper::class, fn () => new ArrayObjectMapper(typeMappers: $classes));
        $this->app->singleton(JsonObjectMapper::class, fn () => new JsonObjectMapper(typeMappers: $classes));
        $this->app->singleton(ObjectMapper::class, fn () => new ArrayObjectMapper());
        $this->app->singleton(CallableDispatcher::class, JacksonLaravelCallableDispatcher::class);
        $this->app->singleton(ControllerDispatcher::class, JacksonLaravelControllerDispatcher::class);
        $this->app->singleton(JacksonLaravelRouteParamResolver::class, JacksonLaravelRouteParamResolver::class);
    }
}
