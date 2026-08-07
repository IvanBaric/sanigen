<?php

declare(strict_types=1);

use IvanBaric\Sanigen\Registries\SanitizerRegistry;
use Tests\SpatieTranslatableTestModel;
use Tests\StructuredTestModel;

test('unicode sanitizer normalizes NFC without transliterating Croatian letters', function () {
    $decomposed = "c\u{0301}";
    $croatian = 'č ć ž š đ Č Ć Ž Š Đ';

    expect(SanitizerRegistry::resolve('unicode')->apply($decomposed))->toBe('ć')
        ->and(SanitizerRegistry::resolve('unicode')->apply($croatian))->toBe($croatian);
});

test('unicode sanitizer rejects invalid UTF-8', function () {
    expect(fn () => SanitizerRegistry::resolve('unicode')->apply("\xC3\x28"))
        ->toThrow(InvalidArgumentException::class, 'Unicode NFC');
});

test('decimal normalizes localized and negative strings while preserving scalar types', function () {
    $model = new class extends StructuredTestModel
    {
        protected array $sanitize = ['settings' => 'decimal'];
    };

    $model->settings = [
        'eu' => '12,50 €',
        'euThousands' => '1.234,56 €',
        'usThousands' => '1,234.56 USD',
        'negative' => '-12,50 €',
        'integer' => 12,
        'float' => 12.5,
        'boolean' => true,
        'null' => null,
    ];

    expect($model->settings)->toBe([
        'eu' => '12.50',
        'euThousands' => '1234.56',
        'usThousands' => '1234.56',
        'negative' => '-12.50',
        'integer' => 12,
        'float' => 12.5,
        'boolean' => true,
        'null' => null,
    ]);
});

test('real Spatie translatable assignment sanitizes text and safe HTML locales', function () {
    $model = new SpatieTranslatableTestModel;
    $model->title = [
        'hr' => '<strong>Hrvatski naslov</strong>',
        'en' => '<strong>English title</strong>',
    ];
    $model->content = [
        'hr' => '<p>Hrvatski <script>alert(1)</script></p>',
        'en' => '<p>English <script>alert(1)</script></p>',
    ];

    expect($model->getTranslations('title'))->toBe([
        'hr' => 'Hrvatski naslov',
        'en' => 'English title',
    ])->and($model->getTranslations('content')['hr'])->toContain('<p>Hrvatski </p>')
        ->and($model->getTranslations('content')['hr'])->not->toContain('<script>')
        ->and($model->getTranslations('content')['en'])->not->toContain('<script>');
});

test('sanitizeAttributes processes every stored Spatie locale together', function () {
    $model = new SpatieTranslatableTestModel;
    $model->setRawAttributes([
        'title' => json_encode([
            'hr' => '<strong>Hrvatski naslov</strong>',
            'en' => '<strong>English title</strong>',
        ]),
    ]);

    expect($model->sanitizeAttributes())->toBeTrue()
        ->and($model->getTranslations('title'))->toBe([
            'hr' => 'Hrvatski naslov',
            'en' => 'English title',
        ]);
});

test('direct Spatie translation APIs sanitize without double processing', function () {
    $model = new SpatieTranslatableTestModel;
    $model->setTranslation('title', 'hr', '<strong>Hrvatski naslov</strong>');
    $model->setTranslations('content', [
        'hr' => '<p>Hrvatski <script>alert(1)</script></p>',
        'en' => '<p>English <script>alert(1)</script></p>',
    ]);

    expect($model->getTranslation('title', 'hr'))->toBe('Hrvatski naslov')
        ->and($model->getTranslations('content')['hr'])->not->toContain('<script>')
        ->and($model->getTranslations('content')['en'])->not->toContain('<script>');
});

test('safe html alias can call its same-named base sanitizer', function () {
    $result = SanitizerRegistry::resolve('safe_html')->apply('<p>Safe<script>alert(1)</script></p>');

    expect($result)->toContain('<p>Safe</p>')->not->toContain('<script>');
});
