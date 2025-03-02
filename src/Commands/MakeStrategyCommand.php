<?php

declare(strict_types=1);

namespace Jcesarbueno\LaravelStrategy\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Number;
use Jcesarbueno\LaravelStrategy\Commands\Concerns\CanManipulateFiles;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\note;
use function Laravel\Prompts\text;

final class MakeStrategyCommand extends Command
{
    use CanManipulateFiles;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:strategy {name?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a new Strategy implementation using Strategy, Factory and Chain of Responsibility Design Patterns';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {

        $strategy = (string) str($this->argument('name') ?? text(
            label: 'What is the strategy name?',
            placeholder: 'PaymentMethod',
            required: true,
        ))
            ->trim('/')
            ->trim('\\')
            ->trim(' ')
            ->replace('/', '\\');

        $strategyClass = (string) str($strategy)
            ->afterLast('\\');

        if ($this->checkForCollision([
            app_path("Strategies/{$strategyClass}/"),
        ])) {
            $this->components->error("Strategy {$strategyClass} already exists. Aborting.");

            return;
        }

        $methods = [];

        while (true) {
            if ($methods !== []) {
                $stringMethods = implode(', ', $methods);
                note("$strategy methods: $stringMethods");
            }

            $methodsCount = $methods === [] ? '1st' : Number::ordinal(count($methods) + 1);

            $method = (string) str(text(
                label: "What is the $methodsCount method name?",
                placeholder: 'Type the method name, or press Enter to finish',
            ));

            if ($method === '' || $method === '0') {
                break;
            }

            $methods[] = $method;
        }

        $implementations = [];

        while (true) {
            if ($implementations !== []) {
                $stringMethods = implode(', ', $implementations);
                note("$strategy implementations: $stringMethods");
            }

            $implementationsCount = $implementations === [] ? '1st' : Number::ordinal(count($implementations) + 1);

            $implementation = (string) str(text(
                label: "What is the $implementationsCount concrete implementation name?",
                placeholder: 'Type the implementation name, or press Enter to finish',
            ));

            if ($implementation === '' || $implementation === '0') {
                break;
            }

            $implementations[] = $implementation;
        }

        $pipeline = confirm('Do you want to create Pipelines (Chain of Responsibility) for the Strategy?', false);

        // Criar os arquivos
        $this->createContract($strategy, $strategyClass, $methods, $pipeline);

        if ($implementations !== []) {
            $this->createImplementations($strategy, $strategyClass, $implementations, $methods, $pipeline);
            $this->createFactory($strategy, $strategyClass, $implementations);
        }

        if ($pipeline) {
            $this->createPipeline($strategy, $strategyClass);
        }

        $this->components->info("Strategy {$strategyClass} created successfully.");
    }

    /**
     * @param  list<string>  $methods
     */
    private function createContract(string $name, string $path, array $methods, bool $pipeline): void
    {
        $formattedMethods = [];

        foreach ($methods as $method) {
            $formattedMethods[] = "    public function {$method}(): void;";
        }

        if ($pipeline) {
            $formattedMethods[] = '    public function getPipelines(): array;';
        }

        $this->copyStubToApp('strategy-contract', "Strategies/{$path}/Contracts/{$name}StrategyContract.php", [
            'name' => $name,
            'methods' => $formattedMethods,
        ]);
    }

    /**
     * @param  list<string>  $implementations
     * @param  list<string>  $methods
     */
    private function createImplementations(string $name, string $path, array $implementations, array $methods, bool $pipeline): void
    {
        foreach ($implementations as $implementation) {
            $formattedMethods = [];

            foreach ($methods as $method) {
                $formattedMethods[] = "    public function {$method}(): void \n    {\n\n    }\n";
            }

            if ($pipeline) {
                $formattedMethods[] = "    public function getPipelines(): array \n    {\n      return [      \n      //\n        ];\n    }";
            }

            $this->copyStubToApp('strategy-implementation', "Strategies/{$path}/Implementations/{$implementation}.php", [
                'name' => $name,
                'implementation' => $implementation,
                'methods' => $formattedMethods,
            ]);
        }
    }

    /**
     * @param  list<string>  $implementations
     */
    private function createFactory(string $name, string $path, array $implementations): void
    {
        $formattedImplementations = [];
        $implementationClasses = [];

        foreach ($implementations as $implementation) {
            $formattedImplementations[] = "            '{$implementation}' => new {$implementation}(),";
            $implementationClasses[] = "use App\\Strategies\\{$path}\\Implementations\\{$implementation};";
        }

        $this->copyStubToApp('strategy-factory', "Strategies/{$path}/Factories/{$name}Factory.php", [
            'name' => $name,
            'implementations' => implode("\n", $formattedImplementations),
            'implementationClasses' => implode("\n", $implementationClasses),
        ]);
    }

    private function createPipeline(string $name, string $path): void
    {
        $this->copyStubToApp('strategy-pipeline', "Strategies/{$path}/Pipelines/{$name}Pipeline.php", [
            'name' => $name,
        ]);
    }
}
