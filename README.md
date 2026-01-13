# PHP Jackson for Laravel

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
config/jackson.php
```

### Example configuration

```php
return [
    'classes' => [
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

## 🧪 Error handling

If parsing fails, php-jackson-laravel converts php-jackson `UnableToParseValue`  into `400 Bad Request` HTTP error responses, ex:
```json
{
  "message": "Unable to parse value at .type",
  "expected": ["AAA", "BBB"],
  "given": "string"
}
```
---

## 🪄 Casts
Model attributes can also be cast using Jackson, all configured classes automatically become castable in models:
- Add the class to the mappers in `config/jackson.php`
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

## 📦 Related packages

- Core mapper: https://github.com/tcds-io/php-jackson
- Symfony integration: https://github.com/tcds-io/php-jackson-symfony
- Guzzle integration: https://github.com/tcds-io/php-jackson-guzzle  
