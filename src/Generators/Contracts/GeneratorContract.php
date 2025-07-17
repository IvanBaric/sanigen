<?php

namespace IvanBaric\Sanigen\Generators\Contracts;

interface GeneratorContract
{
    public function generate(string $field, object $model): mixed;
}