<?php

declare(strict_types=1);

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;
use LengthException;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

final class SafeHtmlSanitizer implements Sanitizer
{
    /**
     * @var list<string>
     */
    private array $dangerousElements = [
        'script',
        'style',
        'iframe',
        'object',
        'embed',
        'svg',
        'math',
        'applet',
        'meta',
        'base',
        'link',
    ];

    public function apply(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $maxLength = $this->maxInputLength();

        if ($maxLength !== -1 && strlen($value) > $maxLength) {
            throw new LengthException("HTML input exceeds the configured maximum of {$maxLength} bytes.");
        }

        $config = (new HtmlSanitizerConfig)
            ->allowSafeElements()
            ->allowLinkSchemes($this->allowedLinkSchemes())
            ->allowMediaSchemes(['http', 'https'])
            ->forceHttpsUrls()
            ->forceAttribute('a', 'rel', 'noopener noreferrer')
            ->withMaxInputLength($maxLength);

        foreach ($this->dangerousElements as $element) {
            $config = $config->dropElement($element);
        }

        return trim((new HtmlSanitizer($config))->sanitize($value));
    }

    private function maxInputLength(): int
    {
        $maxLength = (int) config('sanigen.max_html_input_length', config('sanigen.max_strip_scripts_input_length', 32768));

        return $maxLength > 0 ? $maxLength : 32768;
    }

    /**
     * @return list<string>
     */
    private function allowedLinkSchemes(): array
    {
        $schemes = config('sanigen.safe_html_allowed_schemes', ['http', 'https', 'mailto']);

        if (! is_array($schemes)) {
            return ['http', 'https', 'mailto'];
        }

        return array_values(array_unique(array_filter(
            array_map(static fn (mixed $scheme): string => strtolower(trim((string) $scheme)), $schemes),
            static fn (string $scheme): bool => $scheme !== ''
        )));
    }
}
