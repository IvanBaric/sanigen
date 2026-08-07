<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use IvanBaric\Sanigen\Traits\HasSanitizedTranslations;

class SpatieTranslatableTestModel extends Model
{
    use HasSanitizedTranslations;

    protected $table = 'spatie_translatable_test_models';

    protected $guarded = [];

    public array $translatable = ['title', 'content'];

    protected array $sanitize = [
        'title' => 'text',
        'content' => 'safe_html',
    ];
}
