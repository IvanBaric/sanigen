# Sanigen

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ivanbaric/sanigen.svg?style=flat-square)](https://packagist.org/packages/ivanbaric/sanigen)
[![Total Downloads](https://img.shields.io/packagist/dt/ivanbaric/sanigen.svg?style=flat-square)](https://packagist.org/packages/ivanbaric/sanigen)
[![License](https://img.shields.io/packagist/l/ivanbaric/sanigen.svg?style=flat-square)](https://packagist.org/packages/ivanbaric/sanigen)

Sanigen provides model-level sanitization and generators for teams that want consistency and less repetitive input-cleanup code.

## Quick Start

```bash
composer require ivanbaric/sanigen
```

Recommended: publish the config file.

```bash
php artisan vendor:publish --provider="IvanBaric\Sanigen\SanigenServiceProvider" --tag="config"
```

Define or refine aliases in `config/sanigen.php`, then reuse the same sanitization standards across all your models 🚀

```php
'sanitization_aliases' => [
    'text:title' => 'no_html|no_js|emoji_remove|remove_newlines|trim|single_space|lower|ucfirst',
    'text:secure' => 'no_html|no_js|emoji_remove|trim|single_space',
    'email:clean' => 'trim|lower|email',
    'url:secure' => 'trim|remove_newlines|no_js|url',
    'number:decimal' => 'trim|decimal_only',
];
```

Sanigen gives you two model properties:

- `$sanitize`: applies sanitizer aliases when values are assigned on the model, such as `text:title`, `text:secure`, or `email:clean`
- `$generate`: fills missing attributes during `creating`, such as `slug`, `uuid`, `ulid`, `owner_id`, `team_id`, `carbon` dates, `random_string`, or `unique_string`

```php
use Illuminate\Database\Eloquent\Model;
use IvanBaric\Sanigen\Traits\Sanigen;

class Post extends Model
{
    use Sanigen;

    protected $fillable = ['title', 'content', 'email', 'website'];

    protected $sanitize = [
        'title' => 'text:title',
        'content' => 'text:secure',
        'email' => 'email:clean',
        'website' => 'url:secure',
    ];

    protected $generate = [
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

## Full Reference

<details open>
<summary>Built-in sanitizers</summary>

| Sanitizer | Description | Example |
| --- | --- | --- |
| `alpha_dash` | Keeps letters, numbers, hyphens, underscores | `"Hello-123_!"` -> `"Hello-123_"` |
| `alpha_only` | Keeps only letters | `"Hello123"` -> `"Hello"` |
| `alphanumeric_only` | Keeps only letters and numbers | `"Hello123!"` -> `"Hello123"` |
| `ascii_only` | Keeps only ASCII characters | `"cafe ć"` -> `"cafe "` |
| `decimal_only` | Keeps only digits and decimal point | `"Price: $123.45"` -> `"123.45"` |
| `email` | Sanitizes email addresses | `" USER@EXAMPLE.COM "` -> `"user@example.com"` |
| `emoji_remove` | Removes all emoji characters | `"Hello 👋 World 🌍"` -> `"Hello  World "` |
| `htmlspecialchars` | HTML5-compatible special char conversion | `"<script>"` -> `"&lt;script&gt;"` |
| `json_escape` | Escapes characters for JSON | `"\"Test\""` -> `"\\\"Test\\\""` |
| `lower` | Converts to lowercase | `"Hello"` -> `"hello"` |
| `no_html` | Removes all HTML tags | `"<p>Hello</p>"` -> `"Hello"` |
| `no_js` | JavaScript removal and XSS protection | `"<script>alert(1)</script>Hello"` -> `"Hello"` |
| `numeric_only` | Keeps only digits | `"Price: $123.45"` -> `"12345"` |
| `phone` | Sanitizes phone numbers (E.164-ish) | `"(123) 456-7890"` -> `"+1234567890"` |
| `remove_newlines` | Removes all line breaks | `"Line 1\nLine 2"` -> `"Line 1Line 2"` |
| `single_space` | Normalizes whitespace to single spaces | `"Hello   World"` -> `"Hello World"` |
| `slug` | Creates URL-friendly slug | `"Hello World"` -> `"hello-world"` |
| `strip_tags` | Removes HTML tags except allowed ones | `"<script>Hello</script>"` -> `"Hello"` |
| `trim` | Removes whitespace from beginning and end | `" Hello "` -> `"Hello"` |
| `ucfirst` | Capitalizes first character | `"hello"` -> `"Hello"` |
| `upper` | Converts to uppercase | `"hello"` -> `"HELLO"` |
| `url` | Ensures URLs have a protocol | `"example.com"` -> `"https://example.com"` |

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
    'max_xss_input_length' => 32768,
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
- `allowed_html_tags`: whitelist formatting tags for `strip_tags` and `no_js`
- `max_xss_input_length`: caps very large payloads before running the XSS cleanup pipeline
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
