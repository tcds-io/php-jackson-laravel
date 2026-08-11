<?php

namespace Tcds\Io\Jackson\Laravel\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Contracts\CallableDispatcher;
use Illuminate\Routing\Contracts\ControllerDispatcher;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Tcds\Io\Jackson\ArrayObjectMapper;
use Tcds\Io\Jackson\JsonObjectMapper;
use Tcds\Io\Jackson\Laravel\Http\Dispatchers\JacksonLaravelCallableDispatcher;
use Tcds\Io\Jackson\Laravel\Http\Dispatchers\JacksonLaravelControllerDispatcher;
use Tcds\Io\Jackson\Laravel\Http\Dispatchers\JacksonLaravelResponseWrapper;
use Tcds\Io\Jackson\Laravel\JacksonConfig;
use Tcds\Io\Jackson\Laravel\Mappers\CollectionMapper;
use Tcds\Io\Jackson\ObjectMapper;

class JacksonLaravelObjectMapperProvider extends ServiceProvider
{
    private string $originalConfigFile;
    private string $configFile;

    public function __construct(Application $app)
    {
        parent::__construct($app);

        $this->originalConfigFile = realpath(__DIR__ . '/../../jackson.php') ?: __DIR__ . '/../../jackson.php';
        $this->configFile = $app->basePath('jackson/config.php');
    }

    public function boot(): void
    {
        $this->publishes([$this->originalConfigFile => $this->configFile], 'jackson');
    }

    public function register(): void
    {
        $config = JacksonConfig::fromConfigFile($this->configFile);
        $mappers = [...$config->mappers, ...CollectionMapper::get(Collection::class)];
        // null reader/writer is an explicit opt-out signal ("let the framework resolve this type").
        // php-jackson honors null at runtime via `??` fallback, but its TypeMapper PHPDoc does not yet
        // declare `|null`. Remove the ignores once the upstream type is widened to include null.
        $arrayMapper = new ArrayObjectMapper(typeMappers: $mappers); // @phpstan-ignore-line argument.type
        $jsonMapper = new JsonObjectMapper(typeMappers: $mappers); // @phpstan-ignore-line argument.type

        $this->app->singleton(JacksonConfig::class, fn() => $config);
        $this->app->singleton(ArrayObjectMapper::class, fn() => $arrayMapper);
        $this->app->singleton(JsonObjectMapper::class, fn() => $jsonMapper);
        $this->app->singleton(ObjectMapper::class, fn() => $arrayMapper);

        // scoped, not singleton: these hold the JacksonLaravelRequestDispatcher, which
        // carries the current Request and its merged request data. Scoped instances are
        // flushed between requests on long-running runtimes (Octane), so the chain is
        // rebuilt against the fresh Request instead of leaking the first request's data.
        $this->app->scoped(CallableDispatcher::class, JacksonLaravelCallableDispatcher::class);
        $this->app->scoped(ControllerDispatcher::class, JacksonLaravelControllerDispatcher::class);

        $this->app->singleton(JacksonLaravelResponseWrapper::class, fn() => new JacksonLaravelResponseWrapper(
            mapper: $this->app->get(ObjectMapper::class),
            config: $this->app->get(JacksonConfig::class),
        ));
    }
}
