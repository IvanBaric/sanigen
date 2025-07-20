# Changelog

All notable changes to the Sanigen package will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.3.0] - 2025-07-20

### Added
- Added configurable slug generator with different suffix types (increment, date, uuid)
- Added configuration options for slug generator in config file
- Added ability to specify suffix type directly in the generator key (e.g., 'slugify:title,date')

### Fixed
- Fixed slug generator with date suffix to ensure uniqueness by adding incremental suffix when needed

## [1.2.0] - 2025-07-20

### Added
- Added support for **UUID v4**, **UUID v7**, and **UUID v8** generators

### Removed
- Removed 'generates date offset values' test as offset has been replaced with carbon
- Removed 'generates auth id values' test as auth_id generator has been removed

### Fixed
- Fixed failing tests due to missing generator keys
- Fixed UuidGenerator to handle null parameters correctly
- Updated tests to work with the removal of AuthIdGenerator
- Replaced 'auth_id' generator usage with 'user:id' in tests

## [1.1.0] - 2025-07-20

### Added
- Added Similar Packages section to README.md (fc0fd1e)
- Added support for UUID versions v4, v7, and v8 in UuidGenerator
- Added tests for different UUID versions

### Changed
- Simplified instantiation and improved naming (80a03cd)
- Further simplified generator instantiation (1ac9548)
- Simplified generator instantiation for improved scalability (273a178)
- Refactored Generator Registry: moved user generator from conditional blocks to a map (4062c36)
- Removed AuthIdGenerator, NowGenerator, and DateOffsetGenerator classes for better maintainability

### Fixed
- Fixed incorrect Packagist badge URLs (22b68f2)
- Fixed Packagist badge URLs in README (dfd9842)

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
