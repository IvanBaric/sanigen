<?php

namespace IvanBaric\Sanigen\Registries;

use IvanBaric\Sanigen\Sanitizers\AlnumSanitizer;
use IvanBaric\Sanigen\Sanitizers\AlphaDashSanitizer;
use IvanBaric\Sanigen\Sanitizers\AlphaSanitizer;
use IvanBaric\Sanigen\Sanitizers\AsciiSanitizer;
use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;
use IvanBaric\Sanigen\Sanitizers\DecimalSanitizer;
use IvanBaric\Sanigen\Sanitizers\DigitsSanitizer;
use IvanBaric\Sanigen\Sanitizers\EmailSanitizer;
use IvanBaric\Sanigen\Sanitizers\LowerSanitizer;
use IvanBaric\Sanigen\Sanitizers\PhoneCleanSanitizer;
use IvanBaric\Sanigen\Sanitizers\SlugSanitizer;
use IvanBaric\Sanigen\Sanitizers\SquishSanitizer;
use IvanBaric\Sanigen\Sanitizers\StripEmojiSanitizer;
use IvanBaric\Sanigen\Sanitizers\StripHtmlSanitizer;
use IvanBaric\Sanigen\Sanitizers\StripNewlinesSanitizer;
use IvanBaric\Sanigen\Sanitizers\StripScriptsSanitizer;
use IvanBaric\Sanigen\Sanitizers\StripTagsSanitizer;
use IvanBaric\Sanigen\Sanitizers\TrimSanitizer;
use IvanBaric\Sanigen\Sanitizers\UcfirstSanitizer;
use IvanBaric\Sanigen\Sanitizers\UpperSanitizer;
use IvanBaric\Sanigen\Sanitizers\UrlSanitizer;

class SanitizerRegistry
{
    protected static array $map = [
        'trim' => TrimSanitizer::class,
        'lower' => LowerSanitizer::class,
        'upper' => UpperSanitizer::class,
        'ucfirst' => UcfirstSanitizer::class,
        'squish' => SquishSanitizer::class,
        'strip_newlines' => StripNewlinesSanitizer::class,
        'strip_html' => StripHtmlSanitizer::class,
        'strip_tags' => StripTagsSanitizer::class,
        'strip_scripts' => StripScriptsSanitizer::class,
        'strip_emoji' => StripEmojiSanitizer::class,
        'alpha' => AlphaSanitizer::class,
        'alnum' => AlnumSanitizer::class,
        'alpha_dash' => AlphaDashSanitizer::class,
        'ascii' => AsciiSanitizer::class,
        'digits' => DigitsSanitizer::class,
        'decimal' => DecimalSanitizer::class,
        'email' => EmailSanitizer::class,
        'phone_clean' => PhoneCleanSanitizer::class,
        'url' => UrlSanitizer::class,
        'slug' => SlugSanitizer::class,
    ];

    public static function resolve(string $key): ?Sanitizer
    {
        $aliases = config('sanigen.aliases', []);

        if (isset($aliases[$key])) {
            return new class($aliases[$key]) implements Sanitizer
            {
                /**
                 * @param  string  $pipeline  Pipe-separated list of sanitizer keys
                 */
                public function __construct(public string $pipeline) {}

                /**
                 * Apply all sanitizers in the pipeline sequentially
                 */
                public function apply(string $value): string
                {
                    $sanitizers = explode('|', $this->pipeline);

                    foreach ($sanitizers as $name) {
                        $name = trim($name);
                        if ($name === '') {
                            continue;
                        }

                        $sanitizer = SanitizerRegistry::resolve($name);

                        if ($sanitizer instanceof Sanitizer) {
                            $value = $sanitizer->apply($value);
                        }
                    }

                    return $value;
                }
            };
        }

        $class = static::$map[$key] ?? null;

        if ($class === null) {
            $mode = config('sanigen.missing_sanitizer', 'throw');
            if ($mode === 'ignore') {
                return null;
            }
            if ($mode === 'log') {
                if (function_exists('logger')) {
                    logger()->error("Sanigen: missing sanitizer '{$key}'.");
                }

                return null;
            }

            throw new \InvalidArgumentException("Sanitizer '{$key}' does not exist.");
        }

        return app($class);
    }

    public static function register(string $key, string $class): void
    {
        static::$map[$key] = $class;
    }
}
