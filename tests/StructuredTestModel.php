<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use IvanBaric\Sanigen\Traits\Sanigen;

class StructuredTestModel extends Model
{
    use Sanigen;

    protected $table = 'structured_test_models';

    protected $guarded = [];

    protected array $sanitize = [];

    /** @param array<string, string> $rules */
    public function withSanitizationRules(array $rules): static
    {
        $this->sanitize = $rules;

        return $this;
    }

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'translations' => 'array',
        ];
    }
}
