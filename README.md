# PHP Jackson for Laravel

[![CI](https://github.com/tcds-io/php-jackson-laravel/actions/workflows/integration.yml/badge.svg)](https://github.com/tcds-io/php-jackson-laravel/actions/workflows/integration.yml)

Laravel integration for [tcds-io/php-jackson](https://github.com/tcds-io/php-jackson), a type-safe object mapper inspired by Jackson (Java).

This package lets you:

- Inject **typed objects** (and collections) directly into controllers and route callables
- Deserialize from JSON body, query params, form data, and route params
- Automatically serialize your return values back to JSON using PHP-Jackson
- Cast model attributes

---

## 🚀 Installation

```bash
composer require tcds-io/php-jackson-laravel
```

Laravel auto‑discovers the service provider. No configuration file is needed for the attribute-based setup. If you disabled package discovery, add the provider manually:

### Manually adding the service provider
```php
'providers' => [
    // ...
    Tcds\Io\Jackson\Laravel\Providers\JacksonLaravelObjectMapperProvider::class,
],
```

---

## ⚙️ How it works

1. Mark request DTO parameters with `#[JacksonInject]`.
2. Mark return values that should be serialized with `#[JacksonResponse]`, or return `jackson($value)` when response metadata belongs in the method body.
3. The plugin inspects your **method parameter types** and **PHPDoc generics**.
4. It builds those objects from:
    - Route params (`{id}`)
    - Query / form data
    - JSON body
5. Your return value is serialized using PHP‑Jackson.

---

## 🧩 Controller-based injection & response

```php
use Tcds\Io\Jackson\Laravel\Attributes\JacksonInject;
use Tcds\Io\Jackson\Laravel\Attributes\JacksonResponse;

class FooBarController
{
    /**
     * @param list<Foo> $items
     * @return list<Foo>
     */
    #[JacksonResponse]
    public function list(#[JacksonInject] array $items): array
    {
        return $items;
    }

    #[JacksonResponse]
    public function read(int $id, #[JacksonInject] Foo $foo): Foo
    {
        return new Foo(
            id: $id,
            a: $foo->a,
            b: $foo->b,
            type: $foo->type,
        );
    }
}
```

Routes:

```php
Route::post('/resource', [FooBarController::class, 'list']);
Route::post('/resource/{id}', [FooBarController::class, 'read']);
```

---

## 🧩 Callable routes with typed injection

```php
use Illuminate\Support\Facades\Route;
use Tcds\Io\Jackson\Laravel\Attributes\JacksonInject;
use Tcds\Io\Jackson\Laravel\Attributes\JacksonResponse;

Route::get('/callable/resource/{id}',
    #[JacksonResponse]
    fn (int $id) => new Foo(id: $id, a: "aaa", b: "get", type: Type::AAA)
);

Route::post('/callable/resource',
    #[JacksonResponse]
    fn (#[JacksonInject] Foo $foo) => $foo
);

Route::post('/callable',
    /**
     * @param list<Foo> $items
     * @return list<Foo>
     */
    #[JacksonResponse]
    fn (#[JacksonInject] array $items): array => $items,
);
```

---

## 🏷 Response status and headers

```php
use Tcds\Io\Jackson\Laravel\Attributes\JacksonInject;
use Tcds\Io\Jackson\Laravel\Attributes\JacksonResponse;

class GreetController
{
    #[JacksonResponse(status: 201, headers: ['X-Resource' => 'greeting'])]
    public function __invoke(#[JacksonInject] Greeting $greeting): Greeting
    {
        return $greeting;
    }
}
```

- **`#[JacksonInject]`** on a parameter forces php-jackson to deserialize the request payload into that type, even when the type is not registered in `mappers` (or has been opted out via `reader: null`).
- **`#[JacksonResponse(status: 201)]`** on a method (or callable) serializes the return value via php-jackson and wraps it in a `JsonResponse` with the given status and headers. `status` defaults to `200`.

Both attributes also work on route closures:

```php
Route::post(
    '/greet',
    #[JacksonResponse(status: 201)]
    fn(#[JacksonInject] Greeting $greeting): Greeting => $greeting,
);
```

For cases where the response metadata belongs in the method body, use the `jackson()` helper:

```php
use function Tcds\Io\Jackson\Laravel\jackson;

class GreetController
{
    public function store(#[JacksonInject] Greeting $greeting)
    {
        return jackson($greeting)
            ->status(201)
            ->header('X-Resource', 'greeting');
    }
}
```

---

## 🛠 Extending Jackson configuration

The attribute-based API is the default path for request and response mapping, but the package also has a central configuration file for application-wide behavior. Use it to add global serialization rules, customize request parsing errors, and inject custom params into mapped objects.

### Publish config

Publish the configuration file when you need global mappers, error handlers, custom params, or model casts:

```bash
php artisan vendor:publish --tag=jackson # creates jackson/config.php
```

### Add global mappers

Global mappers tell Jackson to always handle a type in requests and responses without adding attributes to every controller method or route closure. They are useful when a DTO is part of your app-wide API contract, or when you want to define custom read/write behavior once instead of repeating it at each endpoint.

```php
return [
    'mappers' => [
        // Simple automatic request and response mapping
        Address::class => [],

        // Custom readers and writers
        Foo::class => [
            'reader' => fn(array $data) => new Foo($data['a'], $data['b']),
            'writer' => fn(Foo $foo) => ['a' => $foo->a, 'b' => $foo->b],
        ],

        // Use Laravel services to inject values that do not come from the request
        User::class => [
            'reader' => fn () => Auth::user(),
            'writer' => fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                // 'email' => $user->email, // exclude sensitive fields
            ],
        ],
    ],
];
```

With a configured mapper, Jackson can read and write that type without `#[JacksonInject]` or `#[JacksonResponse]`:

```php
class FooBarController
{
    public function read(int $id, Foo $foo): Foo
    {
        return new Foo(
            id: $id,
            a: $foo->a,
            b: $foo->b,
            type: $foo->type,
        );
    }
}
```

Responses serialized this way use status `200` and do not support custom headers. Use `#[JacksonResponse]` or `jackson($value)` when an endpoint needs a different status code or response headers.

### Customizing the error handler

If parsing fails, php-jackson-laravel converts a php-jackson `UnableToParseValue` exception into a `400 Bad Request` response by default:

```json
{
  "message": "Unable to parse value at .type",
  "expected": ["AAA", "BBB"],
  "given": "string"
}
```

Set `errors.request` when your API needs a different response shape or status code. The handler receives an `UnableToParseValue` exception and must return a `Throwable`, typically an `HttpResponseException`.

```php
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Tcds\Io\Jackson\Exception\UnableToParseValue;

return [
    'errors' => [
        'request' => fn(UnableToParseValue $e) => new HttpResponseException(
            new JsonResponse([
                'error' => $e->getMessage(),
                'hint' => 'Check the request body format.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY),
        ),
    ],
    // ...
];
```

The `UnableToParseValue` exception exposes:
- `$e->getMessage()` — human-readable description of the failure
- `$e->expected` — list of accepted values or types
- `$e->given` — the type or value that was received

### Inject custom params

Custom params let you add request-scoped values that do not come from the URL, query string, form data, or JSON body. This is useful for authenticated user IDs, tenant IDs, locale, feature flags, or any value you want available while Jackson builds a request object.

```php
use App\Services\AuthTokenService;
use Psr\Container\ContainerInterface;

return [
    'params' => fn(ContainerInterface $container) => [
        'userId' => $container->get(AuthTokenService::class)->userId(),
    ],
    // ...
];
```

Those values are merged into the data used to build Jackson objects, so a DTO can receive them like any other constructor field:

```php
readonly class InvoiceQuery
{
    public function __construct(
        public int $userId,
        public ?string $customer = null,
    ) {}
}
```

### Model casts

Configured classes automatically become castable in Eloquent models:

```php
class User extends Model
{
    use JacksonCasts;

    protected $fillable = [
        'settings',
    ];

    protected $casts = [
        'settings' => UserSettings::class,
    ];
}

```

---

## 🔧 Development

```bash
composer install
composer tests       # runs cs:check + phpstan
composer cs:fix      # auto-fix code style
```

End-to-end integration tests run a real Laravel app (default Laravel 13). To target Laravel 12:

```bash
LARAVEL_VERSION='^12.0' tests/install.sh
cd tests/blog && php artisan test
```

---

## 📦 Related packages

- Core mapper: https://github.com/tcds-io/php-jackson
- Symfony integration: https://github.com/tcds-io/php-jackson-symfony
- Guzzle integration: https://github.com/tcds-io/php-jackson-guzzle  
