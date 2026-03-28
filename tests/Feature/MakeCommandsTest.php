<?php

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

test('make:sanitizer creates sanitizer class with default suffix and contract', function () {
    $filesystem = new Filesystem();
    $basePath = base_path('tests/tmp/sanitizers_'.Str::random(8));
    $targetPath = $basePath.DIRECTORY_SEPARATOR.'UsernameSanitizer.php';

    try {
        $this->artisan('make:sanitizer', [
            'name' => 'Username',
            '--path' => $basePath,
            '--namespace' => 'App\\Sanitizers',
        ])->assertExitCode(0);

        expect($filesystem->exists($targetPath))->toBeTrue();

        $content = $filesystem->get($targetPath);
        expect($content)->toContain('namespace App\\Sanitizers;');
        expect($content)->toContain('final class UsernameSanitizer implements Sanitizer');
        expect($content)->toContain('use IvanBaric\\Sanigen\\Sanitizers\\Contracts\\Sanitizer;');
    } finally {
        $filesystem->deleteDirectory($basePath);
    }
});

test('make:sanitizer supports subdirectories and studly names', function () {
    $filesystem = new Filesystem();
    $basePath = base_path('tests/tmp/sanitizers_'.Str::random(8));
    $targetPath = $basePath.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'TitleCleanSanitizer.php';

    try {
        $this->artisan('make:sanitizer', [
            'name' => 'admin/title_clean',
            '--path' => $basePath,
            '--namespace' => 'App\\Sanitizers',
        ])->assertExitCode(0);

        expect($filesystem->exists($targetPath))->toBeTrue();
        expect($filesystem->get($targetPath))->toContain('namespace App\\Sanitizers\\Admin;');
    } finally {
        $filesystem->deleteDirectory($basePath);
    }
});

test('make:generator creates generator class with default suffix and contract', function () {
    $filesystem = new Filesystem();
    $basePath = base_path('tests/tmp/generators_'.Str::random(8));
    $targetPath = $basePath.DIRECTORY_SEPARATOR.'SlugGenerator.php';

    try {
        $this->artisan('make:generator', [
            'name' => 'Slug',
            '--path' => $basePath,
            '--namespace' => 'App\\Generators',
        ])->assertExitCode(0);

        expect($filesystem->exists($targetPath))->toBeTrue();

        $content = $filesystem->get($targetPath);
        expect($content)->toContain('namespace App\\Generators;');
        expect($content)->toContain('final class SlugGenerator implements GeneratorContract');
        expect($content)->toContain('use IvanBaric\\Sanigen\\Generators\\Contracts\\GeneratorContract;');
        expect($content)->toContain('public function generate(string $field, object $model): mixed');
    } finally {
        $filesystem->deleteDirectory($basePath);
    }
});

test('make:generator respects force option when file exists', function () {
    $filesystem = new Filesystem();
    $basePath = base_path('tests/tmp/generators_'.Str::random(8));
    $targetPath = $basePath.DIRECTORY_SEPARATOR.'SlugGenerator.php';

    try {
        $filesystem->ensureDirectoryExists($basePath);
        $filesystem->put($targetPath, 'original');

        $this->artisan('make:generator', [
            'name' => 'SlugGenerator',
            '--path' => $basePath,
            '--namespace' => 'App\\Generators',
        ])->assertExitCode(1);

        expect($filesystem->get($targetPath))->toBe('original');

        $this->artisan('make:generator', [
            'name' => 'SlugGenerator',
            '--path' => $basePath,
            '--namespace' => 'App\\Generators',
            '--force' => true,
        ])->assertExitCode(0);

        expect($filesystem->get($targetPath))->not->toBe('original');
        expect($filesystem->get($targetPath))->toContain('final class SlugGenerator implements GeneratorContract');
    } finally {
        $filesystem->deleteDirectory($basePath);
    }
});

