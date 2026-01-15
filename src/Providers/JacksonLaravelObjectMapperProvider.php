<?php

namespace Tcds\Io\Jackson\Laravel\Providers;

use Carbon\Laravel\ServiceProvider;
use Illuminate\Routing\Contracts\CallableDispatcher;
use Illuminate\Routing\Contracts\ControllerDispatcher;
use Illuminate\Support\Collection;
use Tcds\Io\Jackson\ArrayObjectMapper;
use Tcds\Io\Jackson\JsonObjectMapper;
use Tcds\Io\Jackson\Laravel\Http\Dispatchers\JacksonLaravelCallableDispatcher;
use Tcds\Io\Jackson\Laravel\Http\Dispatchers\JacksonLaravelControllerDispatcher;
use Tcds\Io\Jackson\Laravel\Http\Dispatchers\JacksonLaravelResponseWrapper;
use Tcds\Io\Jackson\Laravel\Mappers\CollectionMapper;
use Tcds\Io\Jackson\ObjectMapper;

class JacksonLaravelObjectMapperProvider extends ServiceProvider
{
    public function boot(): void
    {
        $config = __DIR__ . '/../../config/jackson.php';
        $this->publishes([$config => config_path('jackson.php')]);
    }

    public function register(): void
    {
        $mappers = config('jackson.mappers', []);

        $arrayMapper = new ArrayObjectMapper(typeMappers: [...$mappers, ...CollectionMapper::get(Collection::class)]);

        $this->app->singleton(ArrayObjectMapper::class, fn() => $arrayMapper);
        $this->app->singleton(JsonObjectMapper::class, fn() => new JsonObjectMapper(typeMappers: $mappers));
        $this->app->singleton(ObjectMapper::class, fn() => $arrayMapper);

        $this->app->singleton(CallableDispatcher::class, JacksonLaravelCallableDispatcher::class);
        $this->app->singleton(ControllerDispatcher::class, JacksonLaravelControllerDispatcher::class);

        $this->app->singleton(JacksonLaravelResponseWrapper::class, fn() => new JacksonLaravelResponseWrapper(
            mapper: $arrayMapper,
            mappers: $mappers,
        ));
    }
}
