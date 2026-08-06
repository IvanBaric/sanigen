# Sanigen

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ivanbaric/sanigen.svg?style=flat-square)](https://packagist.org/packages/ivanbaric/sanigen)
[![Total Downloads](https://img.shields.io/packagist/dt/ivanbaric/sanigen.svg?style=flat-square)](https://packagist.org/packages/ivanbaric/sanigen)
[![License](https://img.shields.io/packagist/l/ivanbaric/sanigen.svg?style=flat-square)](https://packagist.org/packages/ivanbaric/sanigen)

Sanigen provides declarative sanitization and attribute generators for Eloquent models, so teams can keep input cleanup consistent without repeating pipelines across models.

## Quick Start

```bash
composer require ivanbaric/sanigen
```

Recommended: publish the config file.

```bash
php artisan vendor:publish --provider="IvanBaric\Sanigen\SanigenServiceProvider" --tag="config"
```

Define aliases in `config/sanigen.php`, then reuse the same defaults across your models.

```php
'aliases' => [
    'text' => 'strip_html|strip_emoji|strip_newlines|trim|squish',
    'title' => 'strip_html|strip_emoji|strip_newlines|trim|squish|lower|ucfirst',
    'ascii' => 'strip_html|strip_emoji|strip_newlines|trim|squish|ascii|trim',
    'email' => 'trim|lower|email',
    'url' => 'trim|strip_newlines|url',
    'slug' => 'trim|lower|slug',
    'decimal' => 'trim|decimal',
    'phone' => 'trim|phone_clean',
];
```

Sanigen gives you two model properties:

- `$sanitize`: applies sanitizer aliases from `config/sanigen.php`
- `$generate`: fills empty attributes on `creating` using generator rules

```php
use Illuminate\Database\Eloquent\Model;
use IvanBaric\Sanigen\Traits\Sanigen;

class Post extends Model
{
    use Sanigen;

    protected $fillable = ['name', 'description', 'content', 'email', 'website', 'slug', 'price', 'phone'];

    protected array $sanitize = [
        'name' => 'title',
        'description' => 'text',
        'content' => 'safe_html',
        'email' => 'email',
        'website' => 'url',
        'slug' => 'slug',
        'price' => 'decimal',
        'phone' => 'phone',
    ];

    protected array $generate = [
        'slug' => 'slugify:name',
        'uuid' => 'uuid',
        'owner_id' => 'user:id',
        'team_id' => 'user:current_team_id',
    ];
}
```

```php
$post = Post::create([
    'name' => '  <script>alert(1)</script>my FIRST post  ',
    'description' => '<script>alert(1)</script><p>Hello <strong>world</strong></p>',
    'content' => '<p>Hello <strong>world</strong></p><a href="http://example.com">Read</a>',
    'email' => ' USER@EXAMPLE.COM ',
    'website' => 'example.com',
    'price' => ' EUR 1,234.56 ',
    'phone' => ' +1 (123) 456-7890 ',
]);
```

Result:

```php
[
    'uuid' => '550e8400-e29b-41d4-a716-446655440000',
    'owner_id' => 1,
    'team_id' => 42,
    'name' => 'My first post',
    'description' => 'Hello world',
    'content' => '<p>Hello <strong>world</strong></p><a href="https://example.com" rel="noopener noreferrer">Read</a>',
    'email' => 'user@example.com',
    'website' => 'https://example.com',
    'slug' => 'my-first-post',
    'price' => '1234.56',
    'phone' => '+11234567890',
]
```

## Available Aliases

- `text`: generic cleaned text
- `plain_text`: cleaned multiline text
- `title`: cleaned title-style text
- `ascii`: cleaned ASCII-only text
- `email`: normalized email
- `url`: normalized URL
- `slug`: slug-ready value
- `decimal`: normalized decimal number
- `phone`: normalized phone number

## Class-Level Attributes

Besides model properties, you can use class-level attributes:

```php
use IvanBaric\Sanigen\Attributes\Generate;
use IvanBaric\Sanigen\Attributes\Sanitize;

#[Sanitize([
    'name' => 'title',
    'description' => 'text',
    'email' => 'email',
])]
#[Generate([
    'slug' => 'slugify:name',
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
| `strip_html` | Removes all HTML for ordinary text after dropping dangerous elements | `"<p>Hello</p>"` -> `"Hello"` |
| `strip_tags` | Compatibility wrapper around PHP `strip_tags()`; not an XSS security boundary | `"<p>Hello</p>"` -> `"Hello"` |
| `strip_scripts` | Compatibility rule backed by the parser-based HTML sanitizer, then limited to configured tags | `"<script>alert(1)</script>Hello"` -> `"Hello"` |
| `safe_html` | Parser-based sanitizer for rich HTML rendered unescaped | `"<p>Hello <strong>world</strong></p>"` -> safe HTML |
| `strip_emoji` | Removes emoji characters | `"Hello emoji World"` -> `"Hello  World"` |
| `alpha` | Keeps only letters | `"Hello123"` -> `"Hello"` |
| `alnum` | Keeps only letters and numbers | `"Hello123!"` -> `"Hello123"` |
| `alpha_dash` | Keeps letters, numbers, hyphens, underscores | `"Hello-123_!"` -> `"Hello-123_"` |
| `ascii` | Keeps only ASCII characters | `"cafe ć"` -> `"cafe"` |
| `digits` | Keeps only digits | `"Price: $123.45"` -> `"12345"` |
| `decimal` | Keeps decimal number characters and normalizes separators | `"1,234.56 EUR"` -> `"1234.56"` |
| `email` | Sanitizes email addresses | `" USER@EXAMPLE.COM "` -> `"user@example.com"` |
| `phone_clean` | Sanitizes phone numbers | `"(123) 456-7890"` -> `"+1234567890"` |
| `url` | Validates and normalizes HTTP/HTTPS URLs | `"example.com"` -> `"https://example.com"` |
| `slug` | Creates a URL-friendly slug | `"Hello World"` -> `"hello-world"` |

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

Use it:

```php
protected $generate = [
    'code' => 'coupon_code',
];
```

</details>

## Resanitize Existing Rows

Warning: this command updates existing database records. Run it on a backup-aware deployment path and test it on staging first.

```bash
php artisan sanigen:resanitize "App\Models\Post" --chunk=200
php artisan sanigen:resanitize "App\Models\Post" --dry-run
```

`sanigen:resanitize` validates the model class, refuses invalid chunk sizes, catches `Throwable`, and does not print record values.

## Security Model

Sanigen is one layer in the input lifecycle:

1. Laravel validation rejects invalid data before your model accepts it.
2. Sanigen cleans and normalizes values as they pass through an Eloquent model.
3. Blade escaping protects browser output.

For ordinary user text, keep Blade escaping enabled:

```blade
{{ $post->description }}
```

Only render unescaped HTML when the attribute is explicitly sanitized with `safe_html`:

```blade
{!! $post->content !!}
```

Sanigen is not a replacement for Laravel validation. It only runs when data goes through an Eloquent model. Raw SQL, direct Query Builder updates, and bulk updates that bypass model events can bypass Sanigen.

`strip_tags` is kept for compatibility but is not complete XSS protection. Use `safe_html` for rich HTML. The `url` sanitizer accepts only HTTP and HTTPS by default; disallowed schemes such as `javascript:`, `data:`, `file:` and `ftp:` return an empty string.

Sanitization failures are fail-closed by default:

```php
'failure_mode' => 'throw',
```

Supported modes are `throw`, `null`, and `original`. The `original` mode is only for explicit compatibility migrations because it can preserve unsafe input.

## Unique Values

Generators such as `slugify`, `unique_string`, and `autoincrement` reduce collisions but cannot guarantee uniqueness under parallel requests by themselves. The database must be the final authority for values that must be unique.

Use unique indexes for tenant-scoped values:

```php
$table->unique(['team_id', 'slug']);
$table->unique(['team_id', 'code']);
```

## Production Notes

```php
return [
    'enabled' => true,
    'missing_sanitizer' => 'throw',
    'failure_mode' => 'throw',
    'aliases' => [
        'text' => 'strip_html|strip_emoji|strip_newlines|trim|squish',
        'title' => 'strip_html|strip_emoji|strip_newlines|trim|squish|lower|ucfirst',
        'ascii' => 'strip_html|strip_emoji|strip_newlines|trim|squish|ascii|trim',
        'email' => 'trim|lower|email',
        'url' => 'trim|strip_newlines|url',
        'slug' => 'trim|lower|slug',
        'decimal' => 'trim|decimal',
        'phone' => 'trim|phone_clean',
    ],
    'allowed_html_tags' => '<p><strong><em><a><ul><ol><li><br>',
    'safe_html_allowed_schemes' => ['http', 'https', 'mailto'],
    'allowed_url_schemes' => ['http', 'https'],
    'default_url_scheme' => 'https',
    'encoding' => 'UTF-8',
    'max_html_input_length' => 32768,
    'sanitize_defaults' => [],
    'generate_defaults' => [],
];
```

## Spatie Translatable Support

Sanigen works with Spatie Laravel Translatable because translatable attributes are stored as arrays. Sanigen recursively sanitizes scalar values and preserves array keys and structure.

## Dynamic JSON Attributes

Use a `recursive:` pipeline when a JSON attribute contains dynamic keys. Sanigen applies the selected rule to every nested string while preserving integers, floats, booleans, and null values.

```php
protected array $sanitize = [
    'settings' => 'recursive:plain_text',
];
```

Recursive pipelines keep the configured nesting-depth, item-count, and scalar-length limits. Objects and other unsupported values fail closed.

## Limitations

Sanigen only runs when data goes through an Eloquent model instance. It does not sanitize direct database writes, raw queries, or Query Builder updates that skip model events.

## Tests

```bash
composer install
composer test
vendor/bin/pint --test
vendor/bin/phpstan analyse
composer audit
```

## License

MIT. See [LICENSE.md](LICENSE.md).
