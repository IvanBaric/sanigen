<?php

namespace IvanBaric\Sanigen\Registries;

use IvanBaric\Sanigen\Sanitizers\AlphaDashSanitizer;
use IvanBaric\Sanigen\Sanitizers\AlphanumericOnlySanitizer;
use IvanBaric\Sanigen\Sanitizers\AlphaOnlySanitizer;
use IvanBaric\Sanigen\Sanitizers\AsciiOnlySanitizer;
use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;
use IvanBaric\Sanigen\Sanitizers\DecimalOnlySanitizer;
use IvanBaric\Sanigen\Sanitizers\EmailSanitizer;
use IvanBaric\Sanigen\Sanitizers\EmojiRemoveSanitizer;
use IvanBaric\Sanigen\Sanitizers\EscapeSanitizer;
use IvanBaric\Sanigen\Sanitizers\HtmlSpecialCharsSanitizer;
use IvanBaric\Sanigen\Sanitizers\JsonEscapeSanitizer;
use IvanBaric\Sanigen\Sanitizers\LowerSanitizer;
use IvanBaric\Sanigen\Sanitizers\NoHtmlSanitizer;
use IvanBaric\Sanigen\Sanitizers\NumericOnlySanitizer;
use IvanBaric\Sanigen\Sanitizers\PhoneSanitizer;
use IvanBaric\Sanigen\Sanitizers\RemoveNewlinesSanitizer;
use IvanBaric\Sanigen\Sanitizers\SingleSpaceSanitizer;
use IvanBaric\Sanigen\Sanitizers\SlugSanitizer;
use IvanBaric\Sanigen\Sanitizers\StripTagsSanitizer;
use IvanBaric\Sanigen\Sanitizers\TrimSanitizer;
use IvanBaric\Sanigen\Sanitizers\UcfirstSanitizer;
use IvanBaric\Sanigen\Sanitizers\UpperSanitizer;
use IvanBaric\Sanigen\Sanitizers\UrlSanitizer;
use IvanBaric\Sanigen\Sanitizers\XssSanitizer;

/**
 * Registry for text sanitizers.
 * 
 * This class manages the available sanitizers and resolves them by key.
 * It also supports pipeline sanitization through configuration aliases.
 */
class SanitizerRegistry
{
    /**
     * Map of sanitizer keys to their class names.
     */
    protected static array $map = [
        'trim'              => TrimSanitizer::class,
        'ucfirst'           => UcfirstSanitizer::class,
        'strip_tags'        => StripTagsSanitizer::class,
        'slug'              => SlugSanitizer::class,
        'htmlspecialchars'  => HtmlSpecialCharsSanitizer::class,
        'single_space'      => SingleSpaceSanitizer::class,
        'lower'             => LowerSanitizer::class,
        'upper'             => UpperSanitizer::class,
        'numeric_only'      => NumericOnlySanitizer::class,
        'escape'            => EscapeSanitizer::class,
        'remove_newlines'   => RemoveNewlinesSanitizer::class,
        'no_html'           => NoHtmlSanitizer::class,
        'xss'               => XssSanitizer::class,
        'email'             => EmailSanitizer::class,
        'phone'             => PhoneSanitizer::class,
        'url'               => UrlSanitizer::class,
        'emoji_remove'      => EmojiRemoveSanitizer::class,
        'alphanumeric_only' => AlphanumericOnlySanitizer::class,
        'alpha_only'        => AlphaOnlySanitizer::class,
        'ascii_only'        => AsciiOnlySanitizer::class,
        'decimal_only'      => DecimalOnlySanitizer::class,
        'alpha_dash'        => AlphaDashSanitizer::class,
        'json_escape'       => JsonEscapeSanitizer::class,
    ];

    /**
     * Resolve a sanitizer instance by key or alias.
     *
     * @param string $key The sanitizer key or configuration alias
     * @return Sanitizer|null The resolved sanitizer or null if not found
     */
    public static function resolve(string $key): ?Sanitizer
    {
        // Check for aliases in configuration
        $aliases = config('sanigen.sanitization_aliases', []);

        if (isset($aliases[$key])) {
            return new class($aliases[$key]) implements Sanitizer {
                /**
                 * @param string $pipeline Pipe-separated list of sanitizer keys
                 */
                public function __construct(public string $pipeline) {}

                /**
                 * Apply all sanitizers in the pipeline sequentially
                 */
                public function apply(string $value): string
                {
                    $sanitizers = explode('|', $this->pipeline);

                    foreach ($sanitizers as $name) {
                        $sanitizer = SanitizerRegistry::resolve($name);

                        if ($sanitizer instanceof Sanitizer) {
                            $value = $sanitizer->apply($value);
                        }
                    }

                    return $value;
                }
            };
        }

        // Standard single sanitizer
        $class = static::$map[$key] ?? null;

        return $class ? app($class) : null;
    }

    /**
     * Register a new sanitizer or override an existing one.
     *
     * @param string $key The key to register the sanitizer under
     * @param string $class The fully qualified class name of the sanitizer
     */
    public static function register(string $key, string $class): void
    {
        static::$map[$key] = $class;
    }
}
