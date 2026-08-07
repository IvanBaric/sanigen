<?php

declare(strict_types=1);

namespace IvanBaric\Sanigen\Traits;

use Spatie\Translatable\HasTranslations;

/**
 * Integrates Sanigen with spatie/laravel-translatable when that package is installed.
 */
trait HasSanitizedTranslations
{
    use HasTranslations, Sanigen {
        Sanigen::setAttribute insteadof HasTranslations;
        HasTranslations::setTranslation as private setSpatieTranslation;
        HasTranslations::setTranslations as private setSpatieTranslations;
    }

    private bool $sanigenSettingTranslations = false;

    protected function setSanigenTranslatableAttribute(string $key, mixed $value): static
    {
        if (is_array($value) && (! array_is_list($value) || $value === [])) {
            return $this->setTranslations($key, $value);
        }

        return $this->setTranslation($key, $this->getLocale(), $value);
    }

    public function setTranslation(string $key, string $locale, $value): self
    {
        if ($this->sanigenSettingTranslations) {
            return $this->setSpatieTranslation($key, $locale, $value);
        }

        $sanitized = $this->sanitizeAttribute($key, [$locale => $value]);

        return $this->setSpatieTranslation($key, $locale, $sanitized[$locale]);
    }

    public function setTranslations(string $key, array $translations): self
    {
        $sanitized = $this->sanitizeAttribute($key, $translations);
        $this->sanigenSettingTranslations = true;

        try {
            return $this->setSpatieTranslations($key, $sanitized);
        } finally {
            $this->sanigenSettingTranslations = false;
        }
    }
}
