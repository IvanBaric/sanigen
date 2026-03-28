<?php

namespace IvanBaric\Sanigen\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeGeneratorCommand extends Command
{
    protected $signature = 'make:generator
                            {name : Generator class name (e.g. SlugGenerator or Content/Slug)}
                            {--force : Overwrite existing file}
                            {--path= : Custom output base path}
                            {--namespace= : Custom base namespace}';

    protected $description = 'Create a new Sanigen generator class';

    public function handle(Filesystem $files): int
    {
        [$class, $subNamespace] = $this->parseName((string) $this->argument('name'), 'Generator');

        $basePath = $this->resolveBasePath($this->option('path'), app_path('Generators'));
        $baseNamespace = $this->resolveBaseNamespace($this->option('namespace'), 'App\\Generators');

        $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $subNamespace);
        $targetDirectory = $relativePath !== ''
            ? $basePath.DIRECTORY_SEPARATOR.$relativePath
            : $basePath;
        $targetPath = $targetDirectory.DIRECTORY_SEPARATOR.$class.'.php';

        if ($files->exists($targetPath) && !$this->option('force')) {
            $this->error("Generator already exists: {$targetPath}");

            return Command::FAILURE;
        }

        $files->ensureDirectoryExists($targetDirectory);
        $files->put($targetPath, $this->buildClass($class, $baseNamespace, $subNamespace));

        $this->info("Generator created: {$targetPath}");

        return Command::SUCCESS;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function parseName(string $name, string $requiredSuffix): array
    {
        $normalized = trim(str_replace('/', '\\', $name), '\\ ');

        if ($normalized === '') {
            throw new \InvalidArgumentException('Generator name cannot be empty.');
        }

        $segments = array_values(array_filter(explode('\\', $normalized), static fn (string $segment): bool => $segment !== ''));
        $classBase = array_pop($segments);
        $class = Str::studly($classBase);

        if (!Str::endsWith($class, $requiredSuffix)) {
            $class .= $requiredSuffix;
        }

        $subNamespace = implode('\\', array_map(static fn (string $segment): string => Str::studly($segment), $segments));

        return [$class, $subNamespace];
    }

    private function buildClass(string $class, string $baseNamespace, string $subNamespace): string
    {
        $namespace = $subNamespace !== ''
            ? $baseNamespace.'\\'.$subNamespace
            : $baseNamespace;

        return <<<PHP
<?php

namespace {$namespace};

use IvanBaric\Sanigen\Generators\Contracts\GeneratorContract;

final class {$class} implements GeneratorContract
{
    public function generate(string \$field, object \$model): mixed
    {
        return null;
    }
}

PHP;
    }

    private function resolveBasePath(mixed $optionPath, string $defaultPath): string
    {
        $path = is_string($optionPath) ? trim($optionPath) : '';

        if ($path === '') {
            return $defaultPath;
        }

        if (Str::startsWith($path, ['/', '\\']) || preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) === 1) {
            return $path;
        }

        return base_path($path);
    }

    private function resolveBaseNamespace(mixed $optionNamespace, string $defaultNamespace): string
    {
        $namespace = is_string($optionNamespace) ? trim($optionNamespace) : '';

        if ($namespace === '') {
            return $defaultNamespace;
        }

        return trim($namespace, '\\');
    }
}

