<?php

namespace Tcds\Io\Jackson\Laravel\Providers;

use Carbon\Laravel\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Contracts\CallableDispatcher;
use Illuminate\Routing\Contracts\ControllerDispatcher;
use Illuminate\Support\Collection;
use Tcds\Io\Jackson\ArrayObjectMapper;
use Tcds\Io\Jackson\JsonObjectMapper;
use Tcds\Io\Jackson\Laravel\Http\Dispatchers\JacksonLaravelCallableDispatcher;
use Tcds\Io\Jackson\Laravel\Http\Dispatchers\JacksonLaravelControllerDispatcher;
use Tcds\Io\Jackson\Laravel\Http\Dispatchers\JacksonLaravelResponseWrapper;
use Tcds\Io\Jackson\Laravel\JacksonConfig;
use Tcds\Io\Jackson\Laravel\Mappers\CollectionMapper;
use Tcds\Io\Jackson\ObjectMapper;

/**
 * @phpstan-import-type Mappers from JacksonConfig
 * @phpstan-import-type TypeMappers from ObjectMapper
 */
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
        $vendorMappers = $this->filterNullableEntries($mappers);
        $arrayMapper = new ArrayObjectMapper(typeMappers: $vendorMappers);
        $jsonMapper = new JsonObjectMapper(typeMappers: $vendorMappers);

        $this->app->singleton(JacksonConfig::class, fn() => $config);
        $this->app->singleton(ArrayObjectMapper::class, fn() => $arrayMapper);
        $this->app->singleton(JsonObjectMapper::class, fn() => $jsonMapper);
        $this->app->singleton(ObjectMapper::class, fn() => $arrayMapper);

        $this->app->singleton(CallableDispatcher::class, JacksonLaravelCallableDispatcher::class);
        $this->app->singleton(ControllerDispatcher::class, JacksonLaravelControllerDispatcher::class);

        $this->app->singleton(JacksonLaravelResponseWrapper::class, fn() => new JacksonLaravelResponseWrapper(
            mapper: $this->app->get(ObjectMapper::class),
            config: $this->app->get(JacksonConfig::class),
        ));
    }

    /**
     * @param Mappers $mappers
     * @return TypeMappers
     */
    private function filterNullableEntries(array $mappers): array
    {
        $result = [];
        foreach ($mappers as $type => $mapper) {
            $entry = [];
            if (isset($mapper['reader'])) {
                $entry['reader'] = $mapper['reader'];
            }
            if (isset($mapper['writer'])) {
                $entry['writer'] = $mapper['writer'];
            }
            $result[$type] = $entry;
        }

        return $result;
    }
}
