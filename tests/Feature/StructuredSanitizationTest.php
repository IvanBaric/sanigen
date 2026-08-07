<?php

declare(strict_types=1);

use IvanBaric\Sanigen\Registries\SanitizerRegistry;
use Tests\Fixtures\CountingSanitizer;
use Tests\StructuredTestModel;

test('all rules recurse automatically and preserve structure and scalar types', function () {
    $model = new class extends StructuredTestModel
    {
        protected array $sanitize = ['settings' => 'text'];
    };

    $model->settings = [
        'title' => "  <b>Naslov</b>\r\n drugi red  ",
        'nested' => ['label' => '<i>Vrijednost</i>'],
        'count' => 12,
        'ratio' => 1.5,
        'enabled' => true,
        'optional' => null,
    ];

    expect($model->settings)->toBe([
        'title' => 'Naslov drugi red',
        'nested' => ['label' => 'Vrijednost'],
        'count' => 12,
        'ratio' => 1.5,
        'enabled' => true,
        'optional' => null,
    ]);
});

test('plain text preserves intentional lines and normalizes line endings', function () {
    $model = new class extends StructuredTestModel
    {
        protected array $sanitize = ['settings' => 'plain_text'];
    };

    $model->settings = ['body' => " <b>Prvi</b>\r\nDrugi\rTreći "];

    expect($model->settings['body'])->toBe("Prvi\nDrugi\nTreći");
});

test('deprecated recursive prefix is equivalent to automatic recursion', function (string $rule) {
    $automatic = (new StructuredTestModel)->withSanitizationRules(['settings' => $rule]);
    $automatic->settings = [
        'hr' => '<p>Hrvatski<script>alert(1)</script></p>',
        'nested' => ['en' => " <b>English</b>\r\ntext "],
    ];

    $legacy = (new StructuredTestModel)->withSanitizationRules(['settings' => 'recursive:'.$rule]);
    $legacy->settings = [
        'hr' => '<p>Hrvatski<script>alert(1)</script></p>',
        'nested' => ['en' => " <b>English</b>\r\ntext "],
    ];

    expect($legacy->settings)->toBe($automatic->settings);
})->with(['text', 'plain_text', 'safe_html']);

test('empty recursive pipelines fail clearly', function () {
    $model = new class extends StructuredTestModel
    {
        protected array $sanitize = ['settings' => 'recursive:'];
    };

    expect(fn () => $model->settings = ['value'])
        ->toThrow(InvalidArgumentException::class, 'empty recursive sanitizer rule');
});

test('a root is traversed once and each matching leaf uses one pipeline', function () {
    SanitizerRegistry::register('counting', CountingSanitizer::class);
    CountingSanitizer::$calls = 0;

    $model = new class extends StructuredTestModel
    {
        protected array $sanitize = [
            'settings' => 'counting',
            'settings.contacts.*' => 'counting',
            'settings.contacts.*.email' => 'counting',
        ];
    };

    $model->settings = [
        'title' => ' one ',
        'contacts' => [
            ['name' => ' two ', 'email' => ' three '],
            ['name' => ' four ', 'email' => ' five '],
        ],
    ];

    expect(CountingSanitizer::$calls)->toBe(5)
        ->and($model->settings['contacts'][1]['email'])->toBe('five');
});

test('sanitizeAttributes processes dotted rules by their unique root', function () {
    SanitizerRegistry::register('counting', CountingSanitizer::class);
    CountingSanitizer::$calls = 0;

    $model = new class extends StructuredTestModel
    {
        protected array $sanitize = [
            'settings.title' => 'counting',
            'settings.subtitle' => 'counting',
        ];
    };
    $model->setRawAttributes([
        'settings' => json_encode(['title' => ' one ', 'subtitle' => ' two ', 'other' => ' raw ']),
    ]);

    expect($model->sanitizeAttributes())->toBeTrue()
        ->and(CountingSanitizer::$calls)->toBe(2)
        ->and($model->settings)->toBe(['title' => 'one', 'subtitle' => 'two', 'other' => ' raw ']);
});
