# Sanigen

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ivanbaric/sanigen.svg?style=flat-square)](https://packagist.org/packages/ivanbaric/sanigen)
[![Total Downloads](https://img.shields.io/packagist/dt/ivanbaric/sanigen.svg?style=flat-square)](https://packagist.org/packages/ivanbaric/sanigen)
[![License](https://img.shields.io/packagist/l/ivanbaric/sanigen.svg?style=flat-square)](https://packagist.org/packages/ivanbaric/sanigen)

Sanigen provides declarative sanitization and attribute generators for Laravel Eloquent models.

## Quick Start

```bash
composer require ivanbaric/sanigen
php artisan vendor:publish --provider="IvanBaric\Sanigen\SanigenServiceProvider" --tag="config"
```

```php
use Illuminate\Database\Eloquent\Model;
use IvanBaric\Sanigen\Traits\Sanigen;

class Post extends Model
{
    use Sanigen;

    protected array $sanitize = [
        'title' => 'text',
        'description' => 'plain_text',
        'content' => 'safe_html',
        'email' => 'email',
        'website' => 'url',
        'price' => 'decimal',
    ];
}
```

Sanitization runs when an attribute is assigned through the Eloquent model. All sanitizer rules automatically recurse through arrays while preserving integer, float, boolean, null values, keys, and structure.

Sanitizer classes still receive and return one string. Sanigen's structured engine owns recursion, path matching, type preservation, limits, and conflict handling.

## Structured Values

### Homogeneous JSON

A root rule is the default for every string in that attribute:

```php
protected array $sanitize = [
    'translations' => 'text',
];

$model->translations = [
    'hr' => '<b>Naslov</b>',
    'en' => '<b>Title</b>',
    'published' => true,
    'revision' => 3,
];
```

The two strings are cleaned while `true` remains a boolean and `3` remains an integer. Arrays may be nested to any configured depth.

### Heterogeneous JSON

Use dot notation when different fields need different pipelines:

```php
protected array $sanitize = [
    'settings.title' => 'text',
    'settings.email' => 'email',
    'settings.price' => 'decimal',
];
```

Only existing matching values are sanitized. Missing paths are a no-op, keys are never created, and unrelated fields remain unchanged. If a path ends at an array, its pipeline becomes the default for every string in that subtree.

### Wildcards

`*` matches one existing array key, including numeric and associative keys:

```php
protected array $sanitize = [
    'settings.contacts.*.name' => 'text',
    'settings.contacts.*.email' => 'email',
    'settings.groups.*.members.*.name' => 'text',
];
```

Multiple wildcard levels are supported. Empty arrays and wildcards without matches are no-ops.

Invalid paths fail before the value is written. Empty segments, partial wildcards such as `cont*cts`, and a wildcard root such as `*.email` are rejected.

### Rule Precedence

Each string leaf uses exactly one pipeline:

1. The longer matching path wins.
2. At equal length, the path with more literal segments wins.
3. A root rule is the default for its entire structure.
4. A specific child rule overrides that default.
5. Equal-specificity matches with different pipelines throw an exception.

Declaration order never changes the result:

```php
protected array $sanitize = [
    'settings' => 'text',
    'settings.email' => 'email',
    'settings.contacts.*' => 'text',
    'settings.contacts.*.email' => 'email',
];
```

Sanigen groups these rules under `settings` and traverses that root value once.

## Spatie Translatable

Install Spatie's package and use Sanigen's integration trait instead of importing two conflicting `setAttribute` traits:

```bash
composer require spatie/laravel-translatable
```

```php
use Illuminate\Database\Eloquent\Model;
use IvanBaric\Sanigen\Traits\HasSanitizedTranslations;

class Page extends Model
{
    use HasSanitizedTranslations;

    public array $translatable = ['title', 'content'];

    protected array $sanitize = [
        'title' => 'text',
        'content' => 'safe_html',
    ];
}

$page->title = [
    'hr' => '<strong>Hrvatski naslov</strong>',
    'en' => '<strong>English title</strong>',
];

$page->content = [
    'hr' => '<p>Hrvatski <script>alert(1)</script></p>',
    'en' => '<p>English <script>alert(1)</script></p>',
];
```

Every locale is sanitized automatically. The bridge preserves Spatie's normal JSON storage and translation reads while ensuring Sanigen receives the complete value before it is stored.

## Standard Aliases

The shipped aliases are config-driven and may be replaced in `config/sanigen.php`:

```php
'aliases' => [
    'text' => 'unicode|strip_html|strip_emoji|strip_newlines|trim|squish',
    'plain_text' => 'unicode|strip_html|strip_emoji|normalize_newlines|trim',
    'title' => 'unicode|strip_html|strip_emoji|strip_newlines|trim|squish|lower|ucfirst',
    'ascii' => 'unicode|strip_html|strip_emoji|strip_newlines|trim|squish|ascii|trim',
    'safe_html' => 'unicode|safe_html',
    'email' => 'trim|lower|email',
    'url' => 'trim|strip_newlines|url',
    'slug' => 'trim|lower|slug',
    'decimal' => 'trim|decimal',
    'phone' => 'trim|phone_clean',
],
```

- `text` produces one line of ordinary text and normalizes whitespace.
- `plain_text` keeps intentional line breaks and normalizes `\r\n` and `\r` to `\n`.
- `safe_html` retains allowed rich HTML after parser-based sanitization.

The `unicode` primitive performs only Unicode NFC normalization. It does not transliterate Croatian letters (`č ć ž š đ Č Ć Ž Š Đ`), guess legacy encodings, or repair mojibake. Invalid Unicode fails closed.

The `normalize_newlines` primitive only converts Windows and old Mac line endings to `\n`.

## Decimal Values

`decimal` normalizes textual user input:

```text
12,50 €      -> 12.50
1.234,56 €   -> 1234.56
1,234.56 USD -> 1234.56
-12,50 €     -> -12.50
```

Inside arrays, integers remain integers, floats remain floats, booleans remain booleans, and null remains null.

Sanigen normalizes input. Laravel validation decides whether a value is allowed. An Eloquent cast decides how it is stored and presented. `decimal` is not a validation rule.

## Deprecated `recursive:` Prefix

Since every rule now recurses automatically, these declarations are equivalent:

```php
'settings' => 'text',
'settings' => 'recursive:text',
```

`recursive:` remains accepted for applications upgrading from 1.8.0, but it is deprecated and may be removed in a future major release. New code should use the ordinary rule. An empty `recursive:` expression still throws a clear exception. No runtime deprecation warning is emitted.

## Rule Sources

Rules can come from three places, in this priority order:

1. Model properties (`$sanitize`, `$generate`)
2. Class-level attributes (`#[Sanitize]`, `#[Generate]`)
3. Config defaults (`sanitize_defaults`, `generate_defaults`)

```php
use IvanBaric\Sanigen\Attributes\Generate;
use IvanBaric\Sanigen\Attributes\Sanitize;

#[Sanitize(['title' => 'text', 'email' => 'email'])]
#[Generate(['slug' => 'slugify:title', 'uuid' => 'uuid'])]
class Post extends Model
{
    use Sanigen;
}
```

Sanigen never infers sanitizer rules from database column types.

## Built-in Sanitizers

| Sanitizer | Purpose |
| --- | --- |
| `unicode` | Normalize valid Unicode to NFC |
| `normalize_newlines` | Convert `\r\n` and `\r` to `\n` |
| `trim`, `lower`, `upper`, `ucfirst`, `squish` | Text transformations |
| `strip_newlines`, `strip_html`, `strip_tags`, `strip_emoji` | Plain-text cleanup |
| `safe_html`, `strip_scripts` | Parser-based HTML sanitization |
| `alpha`, `alnum`, `alpha_dash`, `ascii`, `digits` | Character filtering |
| `decimal`, `email`, `phone_clean`, `url`, `slug` | Format normalization |

`strip_tags` is a compatibility wrapper around PHP's `strip_tags()` and is not an XSS boundary. Use `safe_html` for rich HTML that will be rendered unescaped.

## Custom Sanitizers and Aliases

Every custom sanitizer handles one string:

```php
namespace App\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

final class UsernameSanitizer implements Sanitizer
{
    public function apply(string $value): string
    {
        return strtolower(trim($value));
    }
}
```

```php
use IvanBaric\Sanigen\Registries\SanitizerRegistry;

SanitizerRegistry::register('username', \App\Sanitizers\UsernameSanitizer::class);
```

Aliases can contain built-in sanitizers, custom sanitizers, or other aliases. Circular aliases fail clearly. An alias may share a name with a base sanitizer, as the shipped `safe_html` alias does.

```bash
php artisan make:sanitizer Username
php artisan make:sanitizer Admin/TitleClean --force
```

## Generators

The generator API is unchanged:

```php
protected array $generate = [
    'uuid' => 'uuid:v7',
    'slug' => 'slugify:title',
    'code' => 'unique_string:10',
    'expires_at' => 'carbon:+7 days',
    'owner_id' => 'user:id',
];
```

Built-in generators include `uuid`, `ulid`, `autoincrement`, `unique_string`, `random_string`, `slugify`, `carbon`, and `user`.

Custom generators continue to use `GeneratorRegistry::register()` and `GeneratorContract`:

```bash
php artisan make:generator CouponCode
```

Database unique indexes remain the final authority for values that must be unique under concurrent requests.

## Security Model

Sanigen is one layer in the input lifecycle:

1. Laravel validation rejects disallowed input.
2. Sanigen cleans and normalizes values assigned through Eloquent.
3. Eloquent casts control storage and presentation types.
4. Blade escaping protects ordinary output.

Keep Blade escaping enabled for ordinary text:

```blade
{{ $post->description }}
```

Render unescaped content only when it is intentionally sanitized with `safe_html`:

```blade
{!! $post->content !!}
```

Every root traversal enforces:

- `max_nested_depth`
- `max_nested_items`
- `max_scalar_input_length`
- `max_html_input_length` and sanitizer-specific limits

The item counter is shared across the complete root operation. Scalar length is checked before a sanitizer pipeline runs. Objects and resources fail closed with the root and nested path in the exception, without including the submitted value. Sanitization builds a copy and does not partially write an attribute when processing fails.

Sanitizer failures default to:

```php
'failure_mode' => 'throw',
```

Supported modes are `throw`, `null`, and `original`. `original` is a compatibility mode that may preserve unsafe input and should be used only with an explicit migration plan. Missing sanitizers separately support `throw`, `ignore`, and `log`; `throw` is the default.

## Existing Rows

`sanitizeAttributes()` processes each unique top-level attribute once and returns whether anything changed.

The resanitize command applies current rules to stored models:

```bash
php artisan sanigen:resanitize "App\Models\Post" --chunk=200
php artisan sanigen:resanitize "App\Models\Post" --dry-run
```

This command updates records. Test it on staging and use a backup-aware deployment path.

## Limitations

Sanigen runs only when data passes through an Eloquent model or `sanitizeAttributes()` is called. Raw SQL, Query Builder updates, and bulk operations that bypass model assignment are not sanitized.

## Development

```bash
composer install
composer test
vendor/bin/pint --test
vendor/bin/phpstan analyse
composer audit
```

## License

MIT. See [LICENSE.md](LICENSE.md).
