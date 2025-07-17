<?php

namespace IvanBaric\Sanigen\Sanitizers\Contracts;

/**
 * Contract for all sanitizer implementations.
 * 
 * This interface defines the standard method that all sanitizers
 * must implement to transform input strings according to specific rules.
 */
interface Sanitizer
{
    /**
     * Apply sanitization to the input string.
     *
     * @param string $value The input string to sanitize
     * @return string The sanitized string
     */
    public function apply(string $value): string;
}
