<?php

declare(strict_types=1);

use Tests\StructuredTestModel;

test('one root traversal shares item limits across all dot and wildcard rules', function () {
    config()->set('sanigen.max_nested_items', 4);

    $model = new class extends StructuredTestModel
    {
        protected array $sanitize = [
            'settings.contacts.*.name' => 'text',
            'settings.contacts.*.email' => 'email',
        ];
    };

    expect(fn () => $model->settings = [
        'contacts' => [
            ['name' => 'Ivan', 'email' => 'ivan@example.com'],
            ['name' => 'Ana', 'email' => 'ana@example.com'],
        ],
    ])->toThrow(InvalidArgumentException::class, 'item limit');
});

test('unsupported values fail atomically without exposing their content', function () {
    $secret = 'secret-user-value';
    $model = new class extends StructuredTestModel
    {
        protected array $sanitize = ['settings' => 'text'];
    };

    try {
        $model->settings = ['profile' => (object) ['password' => $secret]];
        test()->fail('Expected unsupported object to fail.');
    } catch (InvalidArgumentException $exception) {
        expect($exception->getMessage())->toContain('settings.profile')
            ->not->toContain($secret)
            ->and($model->getRawOriginal('settings'))->toBeNull();
    }
});

test('resources fail closed with a precise path', function () {
    $resource = fopen('php://memory', 'rb');
    $model = new class extends StructuredTestModel
    {
        protected array $sanitize = ['settings' => 'text'];
    };

    try {
        expect(fn () => $model->settings = ['upload' => $resource])
            ->toThrow(InvalidArgumentException::class, 'settings.upload');
    } finally {
        fclose($resource);
    }
});

test('hundreds of nested items remain correct under multiple path rules', function () {
    config()->set('sanigen.max_nested_items', 1500);
    $contacts = [];

    for ($index = 0; $index < 250; $index++) {
        $contacts[] = [
            'name' => " <b>User {$index}</b> ",
            'email' => " USER{$index}@EXAMPLE.COM ",
            'meta' => ['label' => " <i>Label {$index}</i> "],
        ];
    }

    $model = new class extends StructuredTestModel
    {
        protected array $sanitize = [
            'settings.contacts.*' => 'text',
            'settings.contacts.*.email' => 'email',
            'settings.contacts.*.meta.*' => 'plain_text',
        ];
    };
    $model->settings = ['contacts' => $contacts];

    expect($model->settings['contacts'])->toHaveCount(250)
        ->and($model->settings['contacts'][0]['name'])->toBe('User 0')
        ->and($model->settings['contacts'][249]['email'])->toBe('user249@example.com')
        ->and($model->settings['contacts'][149]['meta']['label'])->toBe('Label 149');
});
