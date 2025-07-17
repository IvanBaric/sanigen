# Changelog

All notable changes to the Sanigen package will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-07-17

### Added

- Initial release of Sanigen package
- Core functionality for declarative sanitization and attribute generation for Eloquent models
- Sanigen trait for easy integration with Eloquent models
- HasGenerators trait for automatic value generation
- HasSanitization trait for automatic attribute sanitization

#### Sanitizers

- Text transformations: trim, lower, upper, ucfirst, single_space, remove_newlines
- Content filtering: alpha_only, alphanumeric_only, alpha_dash, numeric_only, decimal_only, ascii_only, emoji_remove
- Security sanitizers: strip_tags, no_html, xss, escape, htmlspecialchars, json_escape
- Format-specific sanitizers: email, phone, url, slug

#### Generators

- Identifier generators: uuid, ulid, autoincrement, unique_code
- Content generators: slugify, random_string
- Date/time generators: now, offset
- User-related generators: user property, auth_id

#### Configuration

- Ability to enable/disable the package functionality
- Customizable sanitization aliases for reusable pipelines
- Configurable allowed HTML tags for sanitizers
- Default encoding settings

### Requirements

- PHP 8.2 or higher
- Laravel 12.0 or higher