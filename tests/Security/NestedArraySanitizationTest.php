<?php

use Tests\SanitizerTestModel;
use Tests\TranslatableTestModel;

test('nested translatable arrays are sanitized recursively while preserving structure', function () {
    $model = new class extends TranslatableTestModel
    {
        protected array $sanitize = [
            'name' => 'strip_scripts|strip_html|trim',
        ];
    };

    $model->name = [
        'hr' => [
            'title' => '<script>alert(1)</script>Naslov',
            'items' => [
                '<p>Prvi</p>',
                '<img src="x" onerror="alert(1)">Drugi',
            ],
        ],
        'en' => [
            'title' => '<svg onload="alert(1)"></svg>Title',
        ],
    ];

    $model->sanitizeAttributes();

    expect($model->name)->toBe([
        'hr' => [
            'title' => 'Naslov',
            'items' => [
                'Prvi',
                'Drugi',
            ],
        ],
        'en' => [
            'title' => 'Title',
        ],
    ]);
});

test('non scalar nested values fail loudly instead of being string cast silently', function () {
    $model = new class extends SanitizerTestModel
    {
        protected array $sanitize = [
            'text_field' => 'trim',
        ];
    };

    expect(fn () => $model->text_field = ['hr' => new stdClass])
        ->toThrow(InvalidArgumentException::class, 'non-scalar value');
});
