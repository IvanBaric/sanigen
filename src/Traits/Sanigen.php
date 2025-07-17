<?php

namespace IvanBaric\Sanigen\Traits;

/**
 * Convenience trait that combines generator and sanitization functionality.
 * 
 * This trait provides a simple way to include both the HasGenerators and
 * HasSanitization traits in a model, enabling automatic value generation
 * and sanitization in a single import.
 *
 */
trait Sanigen
{
    use HasGenerators;
    use HasSanitization;
}