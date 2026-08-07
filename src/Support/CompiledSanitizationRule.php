<?php

declare(strict_types=1);

namespace IvanBaric\Sanigen\Support;

final readonly class CompiledSanitizationRule
{
    /**
     * @param  list<string>  $segments
     */
    public function __construct(
        public string $path,
        public string $root,
        public array $segments,
        public string $pipeline,
    ) {}

    /**
     * @param  list<string>  $leafPath
     */
    public function matches(array $leafPath): bool
    {
        if (count($this->segments) > count($leafPath)) {
            return false;
        }

        foreach ($this->segments as $index => $segment) {
            if ($segment !== '*' && $segment !== $leafPath[$index]) {
                return false;
            }
        }

        return true;
    }

    public function length(): int
    {
        return count($this->segments);
    }

    public function literalCount(): int
    {
        return count(array_filter(
            $this->segments,
            static fn (string $segment): bool => $segment !== '*',
        ));
    }
}
