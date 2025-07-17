<?php

namespace IvanBaric\Sanigen\Traits;

use IvanBaric\Sanigen\Registries\SanitizerRegistry;

/**
 * Provides automatic sanitization for model attributes.
 * 
 * This trait allows models to define a $sanitize property that maps attributes
 * to sanitizer rules. When a model is being updated, the specified sanitizers
 * will be applied to the attribute values.
 */
trait HasSanitization
{

    /**
     * Set a given attribute on the model.
     *
     * This method overrides the default Laravel setAttribute method to apply
     * sanitization rules before the value is cast and saved.
     *
     * @param string $key The attribute name
     * @param mixed $value The attribute value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        if (config('sanigen.enabled', true) === false) {
            return parent::setAttribute($key, $value);
        }

        // Only proceed with sanitization if:
        // 1. The value is not null
        // 2. The sanitize property exists
        // 3. There are sanitization rules for this attribute
        if ($value !== null && 
            property_exists($this, 'sanitize') && 
            isset($this->sanitize[$key])) {
            
            // Only sanitize scalar values that can be converted to strings
            if (is_scalar($value)) {
                try {
                    $rules = explode('|', $this->sanitize[$key]);
                    
                    // Convert to string for sanitization
                    $stringValue = (string) $value;
                    
                    // Apply each sanitizer in the pipe-delimited rules
                    foreach ($rules as $rule) {
                        $sanitizer = SanitizerRegistry::resolve($rule);
                        if ($sanitizer) {
                            $stringValue = $sanitizer->apply($stringValue);
                        }
                    }
                    
                    // Update the value with the sanitized version
                    $value = $stringValue;
                } catch (\Exception $e) {
                    // Log the error but continue with the original value
                    // to prevent breaking the application
                    if (function_exists('logger')) {
                        logger()->error("Sanitization failed for attribute {$key}: " . $e->getMessage());
                    }
                }
            }
        }

        // Continue with the original Laravel setAttribute
        return parent::setAttribute($key, $value);
    }



}
