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
        ->toThrow(InvalidArgumentException::class, 'unsupported value');
});

test('nested values are bounded by configured depth and item limits', function () {
    config()->set('sanigen.max_nested_depth', 2);
    config()->set('sanigen.max_nested_items', 3);

    $model = new class extends SanitizerTestModel
    {
        protected array $sanitize = [
            'text_field' => 'trim',
        ];
    };

    expect(fn () => $model->text_field = ['one' => ['two' => ['three' => 'value']]])
        ->toThrow(InvalidArgumentException::class, 'nesting limit');

    expect(fn () => $model->text_field = ['one', 'two', 'three', 'four'])
        ->toThrow(InvalidArgumentException::class, 'item limit');
});

test('scalar values are bounded before sanitizer pipelines run', function () {
    config()->set('sanigen.max_scalar_input_length', 5);

    $model = new class extends SanitizerTestModel
    {
        protected array $sanitize = [
            'text_field' => 'trim',
        ];
    };

    expect(fn () => $model->text_field = '123456')
        ->toThrow(InvalidArgumentException::class, 'byte input limit');
});

test('recursive rules sanitize every string while preserving scalar types', function () {
    $model = new class extends SanitizerTestModel
    {
        protected array $sanitize = [
            'text_field' => 'recursive:plain_text',
        ];
    };

    $model->text_field = [
        'headline' => '  <b>Apartment</b>  ',
        'nested' => [
            'description' => "Welcome<script>alert(1)</script>\nGuests",
            'enabled' => true,
            'capacity' => 4,
            'rating' => 4.8,
            'empty' => null,
        ],
        'items' => [
            ['label' => '<img src=x onerror=alert(1)>Pool'],
        ],
    ];

    expect($model->text_field)->toBe([
        'headline' => 'Apartment',
        'nested' => [
            'description' => "Welcome\nGuests",
            'enabled' => true,
            'capacity' => 4,
            'rating' => 4.8,
            'empty' => null,
        ],
        'items' => [
            ['label' => 'Pool'],
        ],
    ]);
});

test('recursive rules reject objects and empty pipelines', function () {
    $model = new class extends SanitizerTestModel
    {
        protected array $sanitize = [
            'text_field' => 'recursive:plain_text',
        ];
    };

    expect(fn () => $model->text_field = ['unsafe' => new stdClass])
        ->toThrow(InvalidArgumentException::class, 'unsupported value');

    $invalidModel = new class extends SanitizerTestModel
    {
        protected array $sanitize = [
            'text_field' => 'recursive:',
        ];
    };

    expect(fn () => $invalidModel->text_field = ['value'])
        ->toThrow(InvalidArgumentException::class, 'empty recursive sanitizer rule');
});
