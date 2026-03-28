# Sanigen

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ivanbaric/sanigen.svg?style=flat-square)](https://packagist.org/packages/ivanbaric/sanigen)
[![Total Downloads](https://img.shields.io/packagist/dt/ivanbaric/sanigen.svg?style=flat-square)](https://packagist.org/packages/ivanbaric/sanigen)
[![License](https://img.shields.io/packagist/l/ivanbaric/sanigen.svg?style=flat-square)](https://packagist.org/packages/ivanbaric/sanigen)

Sanigen provides declarative sanitization/normalization and attribute generators for Eloquent models, for teams that want consistency and less repetitive input-cleanup code.
Version 1.5.0 consolidates the current API, generator tooling, and attribute-based configuration model.

## Quick Start

```bash
composer require ivanbaric/sanigen
```

Recommended: publish the config file.

```bash
php artisan vendor:publish --provider="IvanBaric\Sanigen\SanigenServiceProvider" --tag="config"
```

Define or refine aliases in `config/sanigen.php`, then reuse the same sanitization standards across all your models.

```php
'sanitization_aliases' => [
    'text:title' => 'strip_html|strip_scripts|strip_emoji|strip_newlines|trim|squish|lower|ucfirst',
    'text:plain' => 'strip_html|strip_scripts|strip_emoji|strip_newlines|trim|squish',
    'text:strict' => 'strip_html|strip_scripts|strip_emoji|strip_newlines|ascii|trim|squish',
    'email:clean' => 'trim|lower|email',
    'url:secure' => 'trim|strip_newlines|strip_scripts|url',
    'number:decimal' => 'trim|decimal',
];
```

Sanigen gives you two model properties:

- `$sanitize`: applies sanitizer aliases from `config/sanigen.php`
- `$generate`: fills empty attributes on `creating` using generator rules (see **Built-in generators** below)

```php
use Illuminate\Database\Eloquent\Model;
use IvanBaric\Sanigen\Traits\Sanigen;

class Post extends Model
{
    use Sanigen;

    protected $fillable = ['title', 'content', 'email', 'website'];

    protected array $sanitize = [
        'title' => 'text:title',
        'content' => 'text:strict',
        'email' => 'email:clean',
        'website' => 'url:secure',
    ];

    protected array $generate = [
        'slug' => 'slugify:title',
        'uuid' => 'uuid',
        'owner_id' => 'user:id',
        'team_id' => 'user:current_team_id',
    ];
}
```

```php
$post = Post::create([
    'title' => '  <script>alert(1)</script>my FIRST post  ',
    'content' => '<script>alert(1)</script><p>Hello <strong>world</strong></p>',
    'email' => ' USER@EXAMPLE.COM ',
    'website' => 'example.com',
]);
```

Result:

```php
[
    'uuid' => '550e8400-e29b-41d4-a716-446655440000',
    'owner_id' => 1,
    'team_id' => 42,
    'title' => 'My first post',
    'slug' => 'my-first-post',
    'content' => 'Hello world',
    'email' => 'user@example.com',
    'website' => 'https://example.com',
]
```

## Class-Level Attributes (Optional)

Besides model properties, you can use class-level attributes:

```php
use IvanBaric\Sanigen\Attributes\Generate;
use IvanBaric\Sanigen\Attributes\Sanitize;

#[Sanitize([
    'title' => 'text:plain',
    'excerpt' => 'text:strict',
    'email' => 'email:clean',
])]
#[Generate([
    'slug' => 'slugify:title',
    'uuid' => 'uuid',
])]
class Post extends Model
{
    use Sanigen;
}
```

Rule priority is:

1. explicit model properties (`$sanitize`, `$generate`)

2. class-level attributes (`#[Sanitize]`, `#[Generate]`)

3. config defaults (`sanitize_defaults`, `generate_defaults`)

## Full Reference

<details open>
<summary>Built-in sanitizers</summary>

| Sanitizer | Description | Example |
| --- | --- | --- |
| `trim` | Removes whitespace from beginning and end | `" Hello "` -> `"Hello"` |
| `lower` | Converts to lowercase | `"Hello"` -> `"hello"` |
| `upper` | Converts to uppercase | `"hello"` -> `"HELLO"` |
| `ucfirst` | Capitalizes first character | `"hello"` -> `"Hello"` |
| `squish` | Normalizes whitespace to single spaces | `"Hello   World"` -> `"Hello World"` |
| `strip_newlines` | Removes all line breaks | `"Line 1\nLine 2"` -> `"Line 1Line 2"` |
| `strip_html` | Removes all HTML tags | `"<p>Hello</p>"` -> `"Hello"` |
| `strip_tags` | Removes HTML tags except allowed ones | `"<script>Hello</script>"` -> `"Hello"` |
| `strip_scripts` | Removes common script-bearing markup, inline JS handlers, dangerous protocols, and suspicious JS patterns from text input. | `"<script>alert(1)</script>Hello"` -> `"Hello"` |
| `strip_emoji` | Removes all emoji characters | `"Hello 👋 World 🌍"` -> `"Hello  World "` |
| `alpha` | Keeps only letters | `"Hello123"` -> `"Hello"` |
| `alnum` | Keeps only letters and numbers | `"Hello123!"` -> `"Hello123"` |
| `alpha_dash` | Keeps letters, numbers, hyphens, underscores | `"Hello-123_!"` -> `"Hello-123_"` |
| `ascii` | Keeps only ASCII characters | `"cafe ć"` -> `"cafe "` |
| `digits` | Keeps only digits | `"Price: $123.45"` -> `"12345"` |
| `decimal` | Keeps decimal number characters and normalizes separators | `"1,234.56 €"` -> `"1234.56"` |
| `email` | Sanitizes email addresses | `" USER@EXAMPLE.COM "` -> `"user@example.com"` |
| `phone_clean` | Sanitizes phone numbers | `"(123) 456-7890"` -> `"+1234567890"` |
| `url` | Ensures URLs have a protocol | `"example.com"` -> `"https://example.com"` |
| `slug` | Creates URL-friendly slug | `"Hello World"` -> `"hello-world"` |

</details>

<details open>
<summary>Built-in generators</summary>

| Generator | Purpose | Example |
| --- | --- | --- |
| `uuid` | UUID v4 | `550e8400-e29b-41d4-a716-446655440000` |
| `uuid:v7` | UUID v7 | `018f0f4b-9c3a-7c3e-9d9a-2c3c4b5a6d7e` |
| `uuid:v8` | UUID v8 | `018f0f4b-9c3a-8c3e-9d9a-2c3c4b5a6d7e` |
| `ulid` | ULID | `01J3Z3N6K2Z9N0R2Z7T1W5Y8QG` |
| `autoincrement` | next numeric value | `1` -> `2` -> `3` |
| `unique_string:length` | unique random string | `unique_string:8` -> `A1B2C3D4` |
| `random_string:length` | random string | `random_string:16` -> `aZ2kLm9Qp0xYt1uV` |
| `slugify:field` | unique slug from another field | `slugify:title` -> `my-post-title` |
| `slugify:field,date` | unique slug with date suffix | `slugify:title,date` -> `my-post-title-27-03-2026` |
| `slugify:field,uuid` | unique slug with UUID suffix | `slugify:title,uuid` -> `my-post-title-550e8400-e29b-41d4-a716-446655440000` |
| `carbon:+7 days` | Carbon date from a modifier | `carbon:+7 days` -> `2026-04-03 12:00:00` |
| `user:property` | authenticated user property | `user:id` -> `1`, `user:email` -> `user@example.com` |

</details>

<details>
<summary>Custom sanitizers</summary>

Scaffold a sanitizer class:

```bash
php artisan make:sanitizer Username
php artisan make:sanitizer Admin/TitleClean --force
```

```php
namespace App\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

class UsernameSanitizer implements Sanitizer
{
    public function apply(string $value): string
    {
        return strtolower(trim($value));
    }
}
```

Register it:

```php
use IvanBaric\Sanigen\Registries\SanitizerRegistry;

SanitizerRegistry::register('username', \App\Sanitizers\UsernameSanitizer::class);
```

Register custom sanitizers in a service provider, typically in `App\Providers\AppServiceProvider` (either `register()` or `boot()`), or in your own dedicated provider.

Use it:

```php
protected $sanitize = [
    'username' => 'username',
];
```

</details>

<details>
<summary>Custom generators</summary>

Scaffold a generator class:

```bash
php artisan make:generator Slug
php artisan make:generator Content/Slug --force
```

```php
namespace App\Generators;

use IvanBaric\Sanigen\Generators\Contracts\GeneratorContract;

class CouponCodeGenerator implements GeneratorContract
{
    public function generate(string $field, object $model): mixed
    {
        return 'SALE-' . strtoupper(str()->random(8));
    }
}
```

Register it:

```php
use IvanBaric\Sanigen\Registries\GeneratorRegistry;

GeneratorRegistry::register('coupon_code', \App\Generators\CouponCodeGenerator::class);
```

Register custom generators in a service provider, typically in `App\Providers\AppServiceProvider` (either `register()` or `boot()`), or in your own dedicated provider.

Use it:

```php
protected $generate = [
    'code' => 'coupon_code',
];
```

</details>

## Resanitize Existing Rows

Warning: this command updates existing database records. Run it on a backup-aware deployment path, test it on staging first, and choose a conservative `--chunk` size for large tables.

```bash
php artisan sanigen:resanitize "App\Models\Post" --chunk=200
```

## Production Notes

<details>
<summary>Useful config options</summary>

```php
return [
    'enabled' => true,
    'missing_sanitizer' => 'throw', // throw, ignore, log
    'allowed_html_tags' => '<p><strong><em><a><ul><ol><li><br>',
    'encoding' => 'UTF-8',
    'max_strip_scripts_input_length' => 32768,
    'sanitize_defaults' => [],
    'generate_defaults' => [],
    'generator_settings' => [
        'slugify' => [
            'suffix_type' => 'increment', // increment, date, uuid
            'slug_updates_on_save' => false,
            'date_format' => 'd-m-Y',
        ],
    ],
];
```

Why these matter:

- `enabled`: turn the package on or off globally

- `missing_sanitizer`: fail fast in development, or `log` / `ignore` in production if you prefer softer behavior

- `allowed_html_tags`: whitelist formatting tags for `strip_tags` and `strip_scripts`

- `max_strip_scripts_input_length`: caps very large payloads before running `strip_scripts`

- `sanitize_defaults` / `generate_defaults`: lowest-priority fallback rules

- `slug_updates_on_save`: keep `false` if you want stable URLs, enable it if slugs should follow title changes

You can also override slug update behavior per model:

```php
class Post extends Model
{
    use Sanigen;

    protected bool $slugUpdatesOnSave = true;

    protected $generate = [
        'slug' => 'slugify:title',
    ];
}
```

Per-model override wins over the global config.

</details>

## Spatie Translatable Support

Sanigen works with Spatie Laravel Translatable because translatable attributes are stored as arrays and Sanigen sanitizes each scalar item individually.

## Limitations

Sanigen only works when data goes through an Eloquent model instance.

Simple rule: if data goes through `$model`, Sanigen runs. If data goes straight to the database, it does not.

## Tests

Run the package test suite before release:

```bash
composer test
```

## Contributing

Pull requests are welcome.

## License

MIT. See [LICENSE.md](LICENSE.md).

## Support

If Sanigen saves you time, you can support the project here:

[![Buy Me A Coffee](https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png)](https://coff.ee/ivanbaric)
