# Sanigen

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ivanbaric/sanigen.svg?style=flat-square)](https://packagist.org/packages/sanigen/sanigen)
[![Total Downloads](https://img.shields.io/packagist/dt/ivanbaric/sanigen.svg?style=flat-square)](https://packagist.org/packages/ivanbaric/sanigen)
[![License](https://img.shields.io/packagist/l/ivanbaric/sanigen.svg?style=flat-square)](https://packagist.org/packages/ivanbaric/sanigen)

Sanigen is a powerful Laravel package that provides declarative sanitization and attribute generation for Eloquent models. With Sanigen, you can:

- **Automatically sanitize** model attributes using predefined or custom sanitizers
- **Generate values** for model attributes during creation
- **Define reusable sanitization pipelines** through configuration aliases
- **Maintain clean, consistent data** across your application with minimal effort

## Table of Contents

- [Installation](#installation)
- [Basic Configuration](#basic-configuration)
- [Quick Start](#quick-start)
- [Sanitizers](#sanitizers)
- [Generators](#generators)
- [Configuration](#configuration)
- [Advanced Usage](#advanced-usage)
- [Contributing](#contributing)
- [License](#license)

## Installation

### Requirements

- PHP 8.2 or higher
- Laravel 12.0 or higher

### Via Composer

```bash
composer require ivanbaric/sanigen
```

The package will automatically register its service provider if you're using Laravel's package auto-discovery.

### Publish Configuration

Publish the configuration file to customize sanitizers, generators, and other settings:

```bash
php artisan vendor:publish --provider="IvanBaric\Sanigen\SanigenServiceProvider" --tag="config"
```

This will create a `config/sanigen.php` file in your application.

## Basic Configuration

After publishing the configuration, you can customize the package behavior in `config/sanigen.php`:

```php
return [
    // Enable or disable the package functionality
    'enabled' => true,
    
    // Define sanitization aliases (pipelines of sanitizers)
    'sanitization_aliases' => [
        'text:clean' => 'trim|strip_tags|remove_newlines|single_space',
        // ... more aliases
    ],
    
    // Configure allowed HTML tags for sanitizers that strip tags
    'allowed_html_tags' => '<p><b><i><strong><em><ul><ol><li><br><a><h1><h2><h3><h4><h5><h6><table><tr><td><th><thead><tbody><code><pre><blockquote><q><cite><hr><dl><dt><dd>',
    
    // Set default encoding for sanitizers
    'encoding' => 'UTF-8',
];
```

## Quick Start

Add the `Sanigen` trait to your Eloquent model and define sanitization rules and generators:

```php
use Illuminate\Database\Eloquent\Model;
use IvanBaric\Sanigen\Traits\Sanigen;

class Post extends Model
{
    use Sanigen;
    
    // Define attributes to be generated on model creation
    protected $generate = [
        'slug' => 'slugify:title',
        'uuid' => 'uuid',
    ];
    
    // Define sanitization rules for attributes
    protected $sanitize = [
        'title' => 'text:title',
        'content' => 'text:safe',
        'email' => 'email:clean',
    ];
}
```

Now, when you create or update a Post model:

```php
// Creating a new post
$post = Post::create([
    'title' => ' My First Post ',
    'content' => '<script>alert("XSS")</script><p>This is my content</p>',
    'email' => ' USER@EXAMPLE.COM ',
]);

// The model will automatically:
// 1. Generate a slug: 'my-first-post'
// 2. Generate a UUID
// 3. Sanitize the title: 'My first post'
// 4. Sanitize the content by removing the script tag: '<p>This is my content</p>'
// 5. Sanitize the email: 'user@example.com'
```

## Sanitizers

Sanitizers clean and transform string values to ensure data consistency and security.

### Using Sanitizers in Models

Define sanitization rules in your model using the `$sanitize` property:

```php
protected $sanitize = [
    'attribute_name' => 'sanitizer_name',
    'another_attribute' => 'sanitizer1|sanitizer2|sanitizer3',
    'complex_attribute' => 'text:safe', // Using a predefined alias
];
```

Sanitization is applied:
- When creating a model (if using Sanigen trait)
- When updating a model (always)

### Available Sanitizers

Sanigen includes many built-in sanitizers for common use cases:

| Sanitizer | Description                                  | Example                                    |
|-----------|----------------------------------------------|--------------------------------------------|
| `alpha_dash` | Keeps letters, numbers, hyphens, underscores | `"Hello-123_!"` → `"Hello-123_"`           |
| `alpha_only` | Keeps only letters                           | `"Hello123"` → `"Hello"`                   |
| `alphanumeric_only` | Keeps only letters and numbers               | `"Hello123!"` → `"Hello123"`               |
| `ascii_only` | Keeps only ASCII characters                  | Removes non-ASCII Unicode characters       |
| `decimal_only` | Keeps only digits and decimal point          | `"Price: $123.45"` → `"123.45"`            |
| `email` | Sanitizes email addresses                    | `" USER@EXAMPLE.COM "` → `"user@example.com"` |
| `emoji_remove` | Removes all emoji characters                 | Strips Unicode emoji blocks                |
| `escape` | Converts special characters to HTML entities | `"<script>"` → `"&lt;script&gt;"`          |
| `htmlspecialchars` | HTML5-compatible special char conversion     | Similar to `escape` but with HTML5 support |
| `json_escape` | Escapes characters for JSON                  | Escapes quotes, backslashes, etc.          |
| `lower` | Converts to lowercase                        | `"Hello"` → `"hello"`                      |
| `no_html` | Removes all HTML tags                        | `"<p>Hello</p>"` → `"Hello"`               |
| `numeric_only` | Keeps only digits                            | `"Price: $123.45"` → `"12345"`             |
| `phone` | Sanitizes phone numbers (E.164)                   | `"(123) 456-7890"` → `"+1234567890"`       |
| `remove_newlines` | Removes all line breaks                      | Converts multi-line text to single line    |
| `single_space` | Normalizes whitespace to single spaces       | `"Hello  World"` → `"Hello World"`         |
| `slug` | Creates URL-friendly slug                    | `"Hello World"` → `"hello-world"`          |
| `strip_tags` | Removes HTML tags except allowed ones        | `"<script>Hello</script>"` → `"Hello"`     |
| `trim` | Removes whitespace from beginning and end    | `" Hello "` → `"Hello"`                    |
| `ucfirst` | Capitalizes first character                  | `"hello"` → `"Hello"`                      |
| `upper` | Converts to uppercase                        | `"hello"` → `"HELLO"`                      |
| `url` | Ensures URLs have a protocol                 | `"example.com"` → `"https://example.com"`  |
| `xss` | Comprehensive XSS protection                 | Removes scripts, event handlers, etc.      |

### Sanitization Aliases

One of the most powerful features of Sanigen is the ability to define **sanitization aliases** - reusable pipelines of sanitizers that can be applied together.

The package comes with many predefined aliases in the configuration:

```php
// Example aliases from config/sanigen.php
'sanitization_aliases' => [
    'text:clean'      => 'trim|strip_tags|remove_newlines|single_space',
    'text:safe'       => 'trim|single_space|xss',
    'text:secure'     => 'trim|single_space|no_html|strip_tags|xss',
    'text:title'      => 'trim|single_space|no_html|strip_tags|xss|lower|ucfirst',
    'email:clean'     => 'trim|lower|email',
    'url:secure'      => 'trim|remove_newlines|xss|url',
    // ... many more
],
```

Use these aliases in your models:

```php
protected $sanitize = [
    'title' => 'text:title',
    'content' => 'text:safe',
    'email' => 'email:clean',
    'website' => 'url:secure',
];
```

### Creating Custom Sanitizers

You can create your own sanitizers by implementing the `Sanitizer` interface:

```php
namespace App\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

class MyCustomSanitizer implements Sanitizer
{
    public function apply(string $value): string
    {
        // Transform the value
        return $transformed_value;
    }
}
```

Register your custom sanitizer:

```php
// In a service provider
use IvanBaric\Sanigen\Registries\SanitizerRegistry;

SanitizerRegistry::register('my_custom', \App\Sanitizers\MyCustomSanitizer::class);
```

Then use it in your models:

```php
protected $sanitize = [
    'attribute' => 'my_custom',
];
```

## Generators

Generators automatically create values for model attributes during creation.

### Using Generators in Models

Define generators in your model using the `$generate` property:

```php
protected $generate = [
    'attribute_name' => 'generator_name',
    'parameterized_attribute' => 'generator:parameter',
];
```

Generators are applied only when creating a model, and only if the attribute is empty.

> **Note:** If you specify a generator key that doesn't exist, an `InvalidArgumentException` will be thrown with a message indicating the invalid generator key. This helps you quickly identify mistyped generator keys.

### Available Generators

Sanigen includes several built-in generators:

| Generator | Description | Example |
|-----------|-------------|---------|
| `autoincrement` | Increments from the highest existing value | `1`, `2`, `3`, ... |
| `offset:+7 days` | Creates a date with the specified offset | Current date + 7 days |
| `random_string:8` | Generates a random string of specified length | `"a1b2c3d4"` (random string) |
| `slugify:field` | Creates a unique slug from another field (ensures uniqueness by appending incremental suffixes like -1, -2, etc.) | `"my-post-title"` |
| `ulid` | Generates a ULID (sortable identifier) | `"01F8MECHZX3TBDSZ7XR1QKR505"` |
| `unique_code:8` | Generates a unique random string of specified length (ensures uniqueness by checking the database) | `"a1b2c3d4"` (8 chars) |
| `user:property` | Uses a property from the authenticated user | `"john@example.com"` (user's email) |
| `uuid` | Generates a UUID v4 | `"550e8400-e29b-41d4-a716-446655440000"` |

### Parameter Passing

Many generators accept parameters using the colon syntax:

```php
protected $generate = [
    'code' => 'unique_code:6',      // 6-character unique random code (ensures uniqueness)
    'token' => 'random_string:16',  // 16-character random string (no uniqueness check)
    'slug' => 'slugify:title',      // Unique slug based on the title field (with -1, -2 suffixes if needed)
    'expires_at' => 'offset:+30 days', // Date 30 days in the future
    'author_id' => 'user:id',       // Current user's ID
    'team_id' => 'user:current_team_id', // Current user's team ID
    'author_email' => 'user:email', // Current user's email
    'order' => 'autoincrement',     // Next available number (max + 1)
    'uuid' => 'uuid',               // UUID v4: "550e8400-e29b-41d4-a716-446655440000"
    'ulid' => 'ulid'                // ULID: "01F8MECHZX3TBDSZ7XR1QKR505"
];
```

### Creating Custom Generators

You can create your own generators by implementing the `GeneratorContract` interface:

```php
namespace App\Generators;

use IvanBaric\Sanigen\Generators\Contracts\GeneratorContract;

class MyCustomGenerator implements GeneratorContract
{
    public function generate(string $field, object $model): mixed
    {
        // Generate a value
        return $generated_value;
    }
}
```

Register your custom generator:

```php
// In a service provider
use IvanBaric\Sanigen\Registries\GeneratorRegistry;

GeneratorRegistry::register('my_custom', \App\Generators\MyCustomGenerator::class);
```

Then use it in your models:

```php
protected $generate = [
    'attribute' => 'my_custom',
];
```

## Configuration

### Package Status

You can enable or disable the entire package functionality:

```php
// In config/sanigen.php
'enabled' => true, // or false to disable
```

When disabled, no automatic sanitization or generation will occur.

### Sanitization Aliases

The most powerful feature of Sanigen is the ability to define custom sanitization pipelines as aliases. This allows you to:

1. Create reusable sanitization strategies
2. Apply multiple sanitizers with a single alias
3. Standardize sanitization across your application
4. Make your models cleaner and more readable

Define your own aliases in the configuration:

```php
// In config/sanigen.php
'sanitization_aliases' => [
    // Standard text processing
    'text:clean' => 'trim|strip_tags|remove_newlines|single_space',
    
    // Custom aliases for your application
    'username' => 'trim|lower|alphanumeric_only',
    'product:sku' => 'trim|upper|ascii_only',
    'address' => 'trim|single_space|xss|htmlspecialchars',
],
```

Then use these aliases in your models:

```php
protected $sanitize = [
    'username' => 'username',
    'sku' => 'product:sku',
    'shipping_address' => 'address',
];
```

### Allowed HTML Tags

Configure which HTML tags are allowed when using sanitizers like `strip_tags` or `xss`:

```php
// In config/sanigen.php
'allowed_html_tags' => '<p><b><i><strong><em><ul><ol><li><br><a><h1><h2><h3><h4><h5><h6><table><tr><td><th><thead><tbody><code><pre><blockquote><q><cite><hr><dl><dt><dd>',
```

### Default Encoding

Set the default character encoding for sanitizers:

```php
// In config/sanigen.php
'encoding' => 'UTF-8',
```

## Advanced Usage


### Combining Generators and Sanitizers

One powerful feature of Sanigen is the ability to combine generators and sanitizers on the same field. For example, you can generate a unique code and then automatically convert it to uppercase:

```php
class Coupon extends Model
{
    use Sanigen;
    
    protected $generate = [
        'code' => 'unique_code:6', // Generate a 6-character unique random code
    ];
    
    protected $sanitize = [
        'code' => 'upper', // Convert the code to uppercase
    ];
}
```

When you create a new Coupon model:

```php
$coupon = Coupon::create();
```

The flow is:
1. The unique_code generator creates a unique random 6-character code (e.g., "a1b2c3")
2. The uppercase sanitizer converts it to uppercase (e.g., "A1B2C3")

The result is a 6-character uppercase code that is both generated and sanitized automatically.

### Manual Sanitization

You can manually sanitize attributes:

```php
$model->sanitizeAttributes();
```

### Combining with Laravel Validation

Sanigen works well with Laravel's validation:

```php
// In a controller
$validated = $request->validate([
    'title' => 'required|string|max:255',
    'content' => 'required|string',
    'email' => 'required|email',
]);

// Create model with validated data
$post = Post::create($validated);

// Sanigen will automatically:
// 1. Generate any missing attributes
// 2. Sanitize the input according to rules
// 3. Apply $casts after sanitization

// Note: After sanitization is complete, Laravel's $casts will be applied
// to the model attributes. This means your data goes through sanitization
// first, and then type casting occurs, ensuring both clean and properly
// typed data in your models.
```



## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

If you find this package useful, consider buying me a coffee:

[![Buy Me A Coffee](https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png)](https://coff.ee/ivanbaric)