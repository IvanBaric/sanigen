<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use IvanBaric\Sanigen\Traits\Sanigen;

/**
 * Mock of a model with translatable fields.
 * This simulates the behavior of Spatie's HasTranslations trait
 * without requiring the actual package as a dependency.
 */
class TranslatableTestModel extends Model
{
    use Sanigen;
    
    protected $table = 'translatable_test_models';
    
    protected $fillable = [
        'name',
        'description',
    ];
    
    // Define which attributes are translatable
    public $translatable = [
        'name',
        'description',
    ];
    
    // Define sanitization rules for each field
    protected $sanitize = [
        'name' => 'no_js',
        'description' => 'no_js',
    ];
    
    // Store translations
    protected $translations = [];
    
    /**
     * Set a translation for a specific attribute and locale.
     *
     * @param string $attribute
     * @param string $locale
     * @param mixed $value
     * @return $this
     */
    public function setTranslation($attribute, $locale, $value)
    {
        if (!isset($this->translations[$attribute])) {
            $this->translations[$attribute] = [];
        }
        
        $this->translations[$attribute][$locale] = $value;
        
        // Also set the attribute as an array for the sanitization to work
        $this->attributes[$attribute] = json_encode($this->translations[$attribute]);
        
        return $this;
    }
    
    /**
     * Override the getAttribute method to handle JSON encoded translations
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);
        
        // If this is a translatable attribute, decode it from JSON
        if (in_array($key, $this->translatable) && is_string($value)) {
            try {
                return json_decode($value, true) ?: $value;
            } catch (\Exception $e) {
                return $value;
            }
        }
        
        return $value;
    }
    
    /**
     * Override the setAttribute method to handle array values for translatable attributes
     */
    public function setAttribute($key, $value)
    {
        // If this is a translatable attribute and the value is an array, store it as JSON
        if (in_array($key, $this->translatable) && is_array($value)) {
            $this->translations[$key] = $value;
            $this->attributes[$key] = json_encode($value);
            return $this;
        }
        
        return parent::setAttribute($key, $value);
    }
    
    /**
     * Get a translation for a specific attribute and locale.
     *
     * @param string $attribute
     * @param string $locale
     * @return mixed
     */
    public function getTranslation($attribute, $locale)
    {
        // Get the attribute value, which will be decoded from JSON by getAttribute
        $translations = $this->getAttribute($attribute);
        
        // If it's an array (decoded JSON), return the specific locale
        if (is_array($translations) && isset($translations[$locale])) {
            return $translations[$locale];
        }
        
        // Fallback to the translations array if getAttribute didn't work
        if (isset($this->translations[$attribute][$locale])) {
            return $this->translations[$attribute][$locale];
        }
        
        return null;
    }
}