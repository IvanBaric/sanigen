<?php

namespace IvanBaric\Sanigen\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class Sanitize
{
    /**
     * @param  array<string, string>  $rules
     */
    public function __construct(public array $rules) {}
}
