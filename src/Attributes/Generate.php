<?php

namespace IvanBaric\Sanigen\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class Generate
{
    /**
     * @param  array<string, string>  $rules
     */
    public function __construct(public array $rules) {}
}
