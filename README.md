# PHP Jackson for Laravel

[![CI](https://github.com/tcds-io/php-jackson-laravel/actions/workflows/integration.yml/badge.svg)](https://github.com/tcds-io/php-jackson-laravel/actions/workflows/integration.yml)

Laravel integration for [tcds-io/php-jackson](https://github.com/tcds-io/php-jackson), a type-safe object mapper inspired by Jackson (Java).

This package lets you:

- Inject **typed objects** (and collections) directly into controllers and route callables
- Deserialize from JSON body, query params, form data, and route params
- Automatically serialize your return values back to JSON using PHP-Jackson
- Cast models attributes

---

## 🚀 Installation

```bash
composer require tcds-io/php-jackson-laravel
```

Then create the configuration file:
```bash
php artisan vendor:publish --tag=jackson # creates jackson/config.php
```

Laravel auto‑discovers the service provider. No manual configuration needed unless you disabled discovery:

### Manually adding the service provider
```php
'providers' => [
    // ...
    Tcds\Io\Jackson\Laravel\Providers\JacksonLaravelObjectMapperProvider::class,
],
```

---

## ⚙️ How it works

1. The plugin inspects your **method parameter types** and **PHPDoc generics**.
2. It builds those objects from:
    - Route params (`{id}`)
    - Query / form data
    - JSON body
3. Your return value is serialized automatically using PHP‑Jackson.

---

## 🧩 Controller-based injection & response

```php
class FooBarController
{
    /**
     * @param list<Foo> $items
     * @return list<Foo>
     */
    public function list(array $items): array
    {
        return $items;
    }

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

Routes:

```php
Route::post('/resource', [FooBarController::class, 'list']);
Route::post('/resource/{id}', [FooBarController::class, 'read']);
```

---

## 🧩 Callable routes with typed injection

```php
use Illuminate\Support\Facades\Route;

Route::get('/callable/resource/{id}',
    fn (int $id) => new Foo(id: $id, a: "aaa", b: "get", type: Type::AAA)
);

Route::post('/callable/resource',
    fn (Foo $foo) => $foo
);

Route::post('/callable',
    /**
     * @param list<Foo> $items
     * @return list<Foo>
     */
    fn (array $items): array => $items,
);
```

---

## 🛠 Configuring Serializable Objects

To enable automatic request → object → response mapping, register your serializable classes in:

```
jackson/config.php
```

### Example configuration

```php
return [
    'mappers' => [
        // Simple automatic serialization
        Address::class => [],
    
        // Custom readers and writers
        Foo::class => [
            'reader' => fn(array $data) => new Foo($data['a'], $data['b']),
            'writer' => fn(Foo $foo) => ['a' => $foo->a, 'b' => $foo->b],
        ],
    
        // Use Laravel's Auth system to inject the authenticated user
        User::class => [
            'reader' => fn () => Auth::user(),
    
            // Optional: control what is exposed in API responses
            'writer' => fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                // 'email' => $user->email, // exclude sensitive fields
            ],
        ],
    ],
];
```

### How this behaves

- Any controller or callable that includes `User $user` will automatically receive `Auth::user()`.
- Responses containing a `User` instance will use your custom `writer` output.
- Unregistered classes cannot be serialized or deserialized (security-by-default).

---

## 🏷 Attribute-based injection & response

Instead of registering a type globally in `mappers`, you can opt in per parameter or per method using attributes. The attribute always wins over the global config.

```php
use Tcds\Io\Jackson\Laravel\Attributes\Inject;
use Tcds\Io\Jackson\Laravel\Attributes\Respond;

class GreetController
{
    #[Respond(statusCode: 201)]
    public function __invoke(#[Inject] Greeting $greeting): Greeting
    {
        return $greeting;
    }
}
```

- **`#[Inject]`** on a parameter forces php-jackson to deserialize the request payload into that type, even when the type is not registered in `mappers` (or has been opted out via `reader: null`).
- **`#[Respond(statusCode: 201)]`** on a method (or callable) serializes the return value via php-jackson and wraps it in a `JsonResponse` with the given status. `statusCode` defaults to `200`.

Both attributes also work on route closures:

```php
Route::post(
    '/greet',
    #[Respond(statusCode: 201)]
    fn(#[Inject] Greeting $greeting): Greeting => $greeting,
);
```

---

## 🧪 Error handling

If parsing fails, php-jackson-laravel converts a php-jackson `UnableToParseValue` exception into a `400 Bad Request` HTTP response by default:

```json
{
  "message": "Unable to parse value at .type",
  "expected": ["AAA", "BBB"],
  "given": "string"
}
```

### Customizing the error handler

You can replace the default error response by setting `errors.request` in `jackson/config.php`. The handler receives an `UnableToParseValue` exception and must return a `Throwable` (typically an `HttpResponseException`).
This can be changed to handle it any way you like. The below example mimics the default library behaviour:

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

---

## 🪄 Casts
Model attributes can also be cast using Jackson, all configured classes automatically become castable in models:
- Add the class to the mappers in `jackson/config.php`
- Setup attribute casting

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
composer tests       # runs cs:check + phpstan + phpunit
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
