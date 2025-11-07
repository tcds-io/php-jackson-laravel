<?php

namespace Tcds\Io\Laravel\Jackson\Providers;

use Carbon\Laravel\ServiceProvider;
use Illuminate\Routing\Contracts\CallableDispatcher;
use Illuminate\Routing\Contracts\ControllerDispatcher;
use Tcds\Io\Jackson\ArrayObjectMapper;
use Tcds\Io\Jackson\JsonObjectMapper;
use Tcds\Io\Jackson\ObjectMapper;
use Tcds\Io\Laravel\Jackson\Http\Dispatchers\JacksonCallableDispatcher;
use Tcds\Io\Laravel\Jackson\Http\Dispatchers\JacksonControllerDispatcher;
use Tcds\Io\Laravel\Jackson\Http\JacksonRequestParser;

class JacksonObjectMapperProvider extends ServiceProvider
{
    public function boot(): void
    {
        $config = __DIR__ . '/../../config/serializer.php';
        $this->publishes([$config => config_path('serializer.php')]);
    }

    public function register(): void
    {
        $classes = config('serializer.classes', []);

        $this->app->singleton(ArrayObjectMapper::class, fn() => new ArrayObjectMapper(typeMappers: $classes));
        $this->app->singleton(JsonObjectMapper::class, fn() => new JsonObjectMapper(typeMappers: $classes));
        $this->app->singleton(ObjectMapper::class, fn() => new ArrayObjectMapper());
        $this->app->singleton(CallableDispatcher::class, JacksonCallableDispatcher::class);
        $this->app->singleton(ControllerDispatcher::class, JacksonControllerDispatcher::class);
        $this->app->singleton(JacksonRequestParser::class, JacksonRequestParser::class);

        foreach ($classes as $class => $setup) {
            $this->app->bind($class, fn() => $this->app->get(JacksonRequestParser::class)->parseRequest($class));
        }
    }
}
