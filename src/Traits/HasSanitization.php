<?php

namespace IvanBaric\Sanigen\Traits;

use IvanBaric\Sanigen\Registries\SanitizerRegistry;

/**
 * Provides automatic sanitization for model attributes.
 * 
 * This trait allows models to define a $sanitize property that maps attributes
 * to sanitizer rules. When a model is being updated, the specified sanitizers
 * will be applied to the attribute values.
 * 
 * It also supports array values like those used by Spatie's translatable package,
 * where translations are stored as arrays (e.g., $name['hr'] = "<script>alert("xss")</script>smart").
 * In this case, each translation will be sanitized individually.
 */
trait HasSanitization
{

    /**
     * Sanitize a single attribute value based on defined rules.
     *
     * @param string $key The attribute name
     * @param mixed $value The attribute value
     * @return mixed The sanitized value or original value if sanitization is not applicable
     * 
     * This method handles both scalar values and arrays (like those used by Spatie's translatable package).
     * For array values, each element will be sanitized individually using the same rules.
     */
    protected function sanitizeAttribute($key, $value)
    {
        // Skip sanitization if disabled, value is null, or no rules exist
        if (config('sanigen.enabled', true) === false || 
            $value === null || 
            !property_exists($this, 'sanitize') || 
            !isset($this->sanitize[$key])) {
            return $value;
        }
        
        // Handle array values (like Spatie translatable fields)
        if (is_array($value)) {
            $sanitizedArray = [];
            foreach ($value as $locale => $localeValue) {
                $sanitizedArray[$locale] = $this->sanitizeValue($key, $localeValue);
            }
            return $sanitizedArray;
        }
        
        // For scalar values, sanitize directly
        return $this->sanitizeValue($key, $value);
    }
    
    /**
     * Sanitize a single value based on the rules for a given attribute.
     *
     * @param string $key The attribute name (for rules lookup)
     * @param mixed $value The value to sanitize
     * @return mixed The sanitized value or original value if sanitization is not applicable
     */
    protected function sanitizeValue($key, $value)
    {
        // Only sanitize scalar values that can be converted to strings
        if (!is_scalar($value)) {
            return $value;
        }
        
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
            
            return $stringValue;
        } catch (\InvalidArgumentException $e) {
            // Re-throw InvalidArgumentException for non-existent sanitizers
            throw $e;
        } catch (\Exception $e) {
            // Log other errors but continue with the original value
            if (function_exists('logger')) {
                logger()->error("Sanitization failed for attribute {$key}: " . $e->getMessage());
            }
            return $value;
        }
    }

    /**
     * Sanitize all attributes based on defined rules.
     * 
     * This method applies sanitization rules to all attributes defined in the $sanitize property.
     * It returns true if any attributes were modified during sanitization.
     * 
     * It handles both scalar values and arrays (like those used by Spatie's translatable package),
     * applying sanitization to each array element individually.
     *
     * @return bool Whether any attributes were modified
     */
    public function sanitizeAttributes(): bool
    {
        if (config('sanigen.enabled', true) === false || 
            !property_exists($this, 'sanitize') || 
            empty($this->sanitize)) {
            return false;
        }
        
        $updated = false;
        
        foreach ($this->sanitize as $attribute => $rules) {
            $originalValue = $this->{$attribute};
            
            // Skip null values
            if ($originalValue === null) {
                continue;
            }
            
            // Process both scalar values and arrays (like Spatie translatable fields)
            $sanitizedValue = $this->sanitizeAttribute($attribute, $originalValue);
            
            // Only update if the value has changed
            if ($sanitizedValue !== $originalValue) {
                $this->{$attribute} = $sanitizedValue;
                $updated = true;
            }
        }
        
        return $updated;
    }

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
        // Apply sanitization if applicable
        $value = $this->sanitizeAttribute($key, $value);
        
        // Continue with the original Laravel setAttribute
        return parent::setAttribute($key, $value);
    }



}
