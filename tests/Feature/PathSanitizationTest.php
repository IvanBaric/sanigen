<?php

declare(strict_types=1);

use Tests\StructuredTestModel;

test('dot rules sanitize heterogeneous JSON without creating missing keys', function () {
    $model = new class extends StructuredTestModel
    {
        protected array $sanitize = [
            'settings.title' => 'text',
            'settings.email' => 'email',
            'settings.price' => 'decimal',
            'settings.missing' => 'text',
            'settings.optional' => 'text',
            'settings.section' => 'text',
        ];
    };

    $model->settings = [
        'title' => '<b>Naslov</b>',
        'email' => ' USER@EXAMPLE.COM ',
        'price' => '12,50 €',
        'untouched' => '<b>Ne diraj</b>',
        'optional' => null,
        'section' => ['heading' => '<i>Podnaslov</i>', 'count' => 3],
    ];

    expect($model->settings)->toBe([
        'title' => 'Naslov',
        'email' => 'user@example.com',
        'price' => '12.50',
        'untouched' => '<b>Ne diraj</b>',
        'optional' => null,
        'section' => ['heading' => 'Podnaslov', 'count' => 3],
    ])->not->toHaveKey('missing');
});

test('wildcards support lists associative keys numeric indices and multiple levels', function () {
    $model = new class extends StructuredTestModel
    {
        protected array $sanitize = [
            'settings.contacts.*.name' => 'text',
            'settings.contacts.*.email' => 'email',
            'settings.groups.*.members.*.name' => 'text',
            'settings.matrix.1.*' => 'text',
        ];
    };

    $model->settings = [
        'contacts' => [
            ['name' => '<b>Ivan</b>', 'email' => ' IVAN@EXAMPLE.COM ', 'role' => '<b>admin</b>'],
            'editor' => ['name' => '<i>Marko</i>', 'email' => ' MARKO@EXAMPLE.COM '],
        ],
        'groups' => ['main' => ['members' => [['name' => '<b>Ana</b>']]]],
        'matrix' => [0 => ['raw' => '<b>A</b>'], 1 => ['clean' => '<b>B</b>']],
        'empty' => [],
    ];

    expect($model->settings['contacts'][0])->toBe([
        'name' => 'Ivan',
        'email' => 'ivan@example.com',
        'role' => '<b>admin</b>',
    ])->and($model->settings['contacts']['editor']['name'])->toBe('Marko')
        ->and($model->settings['groups']['main']['members'][0]['name'])->toBe('Ana')
        ->and($model->settings['matrix'][0]['raw'])->toBe('<b>A</b>')
        ->and($model->settings['matrix'][1]['clean'])->toBe('B')
        ->and($model->settings['empty'])->toBe([]);
});

test('specific rules override defaults independent of declaration order', function (array $rules) {
    $model = (new StructuredTestModel)->withSanitizationRules($rules);

    $model->settings = [
        'label' => '<b>Default</b>',
        'email' => ' USER@EXAMPLE.COM ',
        'contacts' => [
            ['name' => '<i>Ivan</i>', 'email' => ' IVAN@EXAMPLE.COM '],
        ],
    ];

    expect($model->settings)->toBe([
        'label' => 'Default',
        'email' => 'user@example.com',
        'contacts' => [['name' => 'Ivan', 'email' => 'ivan@example.com']],
    ]);
})->with([
    'defaults first' => [[
        'settings' => 'text',
        'settings.email' => 'email',
        'settings.contacts.*' => 'text',
        'settings.contacts.*.email' => 'email',
    ]],
    'specific rules first' => [[
        'settings.contacts.*.email' => 'email',
        'settings.contacts.*' => 'text',
        'settings.email' => 'email',
        'settings' => 'text',
    ]],
]);

test('equal specificity conflicts fail closed', function () {
    $model = new class extends StructuredTestModel
    {
        protected array $sanitize = [
            'settings.*.email' => 'email',
            'settings.contacts.*' => 'text',
        ];
    };

    expect(fn () => $model->settings = ['contacts' => ['email' => ' USER@EXAMPLE.COM ']])
        ->toThrow(InvalidArgumentException::class, 'equal specificity');
});

test('invalid paths fail clearly', function (string $path, string $message) {
    $model = (new StructuredTestModel)->withSanitizationRules([$path => 'text']);

    expect(fn () => $model->settings = ['value'])
        ->toThrow(InvalidArgumentException::class, $message);
})->with([
    'empty' => ['', 'cannot be empty'],
    'leading dot' => ['.settings', 'empty segment'],
    'trailing dot' => ['settings.', 'empty segment'],
    'empty segment' => ['settings..email', 'empty segment'],
    'partial wildcard' => ['settings.cont*cts.email', 'invalid wildcard'],
    'root wildcard' => ['*.email', 'root attribute'],
]);
