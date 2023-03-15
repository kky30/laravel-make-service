<?php

namespace kky30\LaravelMakeService;

use Illuminate\Console\Concerns\CreatesMatchingTest;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use InvalidArgumentException;

class MakeService extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service
                            {name : The name of the service layer to create}
                            {--M|model : The name of the model to inject into the service layer}
                            {--N|model-name= : The name of the model to inject into the service layer}
                            {--F|force : Overwrite existing files with the same name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Service class and inject the model into it.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Service';

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        if ($this->isUseInjectionModal()) {
            return $this->resolveStubPath('/stubs/service.model.stub');
        }

        return $this->resolveStubPath('/stubs/service.stub');
    }

    /**
     * Resolve the fully-qualified path to the stub.
     */
    protected function resolveStubPath(string $stub): string
    {
        $customPath = $this->laravel->basePath(trim($stub, '/'));

        return file_exists($customPath) ? $customPath : __DIR__.$stub;
    }

    /**
     * Get the default namespace for the class.
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Services';
    }

    /**
     * Tagging the entered service layer name with the Service suffix
     *
     * @return string
     */
    protected function getNameInputWithService(): string
    {
        return trim($this->argument('name')).'Service';
    }

    /**
     * Get class names without namespaces from name input
     *
     * @return string
     */
    protected function getNameInputBasename(): string
    {
        return basename($this->getNameInput());
    }

    /**
     * Get the model name to inject
     *
     * @return string
     */
    protected function getModelName(): string
    {
        return trim(basename($this->option('model-name'))) ?: $this->getNameInputBasename();
    }

    /**
     * Check if model injection is enabled
     *
     * @return bool
     */
    protected function isUseInjectionModal(): bool
    {
        return $this->option('model') || $this->option('model-name');
    }

    /**
     * Build the Service layer replacement values.
     *
     * @throws FileNotFoundException
     */
    protected function buildServiceReplacements(string $name): string
    {
        $stub = $this->files->get($this->getStub());

        if ($this->isUseInjectionModal()) {
            $replacements = [
                '{{ model }}' => $this->parseModel($this->getModelName()),
                '{{ upperModel }}' => ucfirst($this->getModelName()),
                '{{ lowerModel }}' => strtolower($this->getModelName()),
            ];

            foreach ($replacements as $key => $value) {
                $stub = str_replace($key, $value, $stub);
            }
        }

        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }

    /**
     * Build the model replacement values.
     */
    protected function buildModelReplacements(): void
    {
        if (! $this->isUseInjectionModal()) {
            return;
        }

        $modelClass = $this->parseModel($this->getModelName());

        $msg = "A {$modelClass} model does not exist. Do you want to generate it?";

        if (! class_exists($modelClass) && $this->components->confirm($msg, true)) {
            $this->call('make:model', ['name' => $modelClass]);
        }
    }

    /**
     * Get the fully-qualified model class name.
     */
    protected function parseModel(string $model): string
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $model)) {
            throw new InvalidArgumentException('Model name contains invalid characters.');
        }

        return $this->qualifyModel($model);
    }

    /**
     * Execute the console command.
     *
     * @throws FileNotFoundException
     */
    public function handle()
    {
        if ($this->isReservedName($this->getNameInput())) {
            $this->components->error('The name "'.$this->getNameInput().'" is reserved by PHP.');

            return;
        }

        $this->buildModelReplacements();

        $name = $this->qualifyClass($this->getNameInputWithService());
        $path = $this->getPath($name);

        if ((! $this->hasOption('force') || ! $this->option('force'))
            && $this->alreadyExists($this->getNameInputWithService())) {
            $this->components->error($this->type.' already exists.');

            return;
        }

        $this->makeDirectory($path);

        $this->files->put($path, $this->sortImports($this->buildServiceReplacements($name)));

        $info = $this->type;

        if (in_array(CreatesMatchingTest::class, class_uses_recursive($this), true) && $this->handleTestCreation($path)) {
            $info .= ' and test';
        }

        $this->components->info(sprintf('%s [%s] created successfully.', $info, $path));
    }
}
