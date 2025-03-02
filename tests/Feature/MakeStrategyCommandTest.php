<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

use function Pest\Laravel\artisan;

function createStrategy(bool $createPipeline): void
{
    artisan('make:strategy PaymentMethod')
        ->expectsQuestion('What is the 1st method name?', 'pay')
        ->expectsQuestion('What is the 2nd method name?', 'restore')
        ->expectsQuestion('What is the 3rd method name?', '')
        ->expectsQuestion('What is the 1st concrete implementation name?', 'CreditCard')
        ->expectsQuestion('What is the 2nd concrete implementation name?', 'DebitCard')
        ->expectsQuestion('What is the 3rd concrete implementation name?', '')
        ->expectsConfirmation('Do you want to create Pipelines (Chain of Responsibility) for the Strategy?', $createPipeline ? 'yes' : 'no')
        ->assertExitCode(0);
}

function createBlankStrategy(): void
{
    artisan('make:strategy')
        ->expectsQuestion('What is the strategy name?', 'PaymentMethod')
        ->expectsQuestion('What is the 1st method name?', '')
        ->expectsQuestion('What is the 1st concrete implementation name?', '')
        ->expectsConfirmation('Do you want to create Pipelines (Chain of Responsibility) for the Strategy?', 'no')
        ->assertExitCode(0);
}

test('it creates a strategy without inform the name and do not create another with the same name', function () {
    File::deleteDirectory(app_path('Strategies'));
    createBlankStrategy();

    $interface = App\Strategies\PaymentMethod\Contracts\PaymentMethodStrategyContract::class;

    expect(interface_exists($interface))->toBeTrue();

    artisan('make:strategy')
        ->expectsQuestion('What is the strategy name?', 'PaymentMethod')
        ->expectsOutputToContain('Strategy PaymentMethod already exists. Aborting.')
        ->assertExitCode(0);
});

test('it creates a Strategy with methods and the methods exists', function () {
    File::deleteDirectory(app_path('Strategies'));
    createStrategy(false);

    $interface = App\Strategies\PaymentMethod\Contracts\PaymentMethodStrategyContract::class;
    $file = File::get(app_path('Strategies/PaymentMethod/Contracts/PaymentMethodStrategyContract.php'));

    expect(interface_exists($interface))->toBeTrue()
        ->and($file)->toContain('public function pay()')
        ->and($file)->toContain('public function restore()');

});

test('it has implementations with correct methods', function () {

    $creditCard = new ReflectionClass(App\Strategies\PaymentMethod\Implementations\CreditCard::class);
    $debitCard = new ReflectionClass(App\Strategies\PaymentMethod\Implementations\DebitCard::class);

    expect($creditCard->getMethods())->toHaveCount(2)
        ->and($debitCard->getMethods())->toHaveCount(2)
        ->and($creditCard->implementsInterface(App\Strategies\PaymentMethod\Contracts\PaymentMethodStrategyContract::class))->toBeTrue()
        ->and($debitCard->implementsInterface(App\Strategies\PaymentMethod\Contracts\PaymentMethodStrategyContract::class))->toBeTrue()
        ->and($creditCard->hasMethod('pay'))->toBeTrue()
        ->and($creditCard->hasMethod('restore'))->toBeTrue()
        ->and($debitCard->hasMethod('pay'))->toBeTrue()
        ->and($debitCard->hasMethod('restore'))->toBeTrue();
});

test('the factory can create the implementations', function () {
    $factory = new ReflectionClass(App\Strategies\PaymentMethod\Factories\PaymentMethodFactory::class);

    expect($factory->hasMethod('make'))->toBeTrue();

    $method = $factory->getMethod('make');

    expect($method->getReturnType()->getName())->toBe(App\Strategies\PaymentMethod\Contracts\PaymentMethodStrategyContract::class);

    $method->setAccessible(true);

    $creditCard = $method->invoke(null, 'CreditCard');
    $debitCard = $method->invoke(null, 'DebitCard');

    expect($creditCard)->toBeInstanceOf(App\Strategies\PaymentMethod\Implementations\CreditCard::class)
        ->and($debitCard)->toBeInstanceOf(App\Strategies\PaymentMethod\Implementations\DebitCard::class);
});

test('it creates a Strategy and Pipeline', function () {
    File::deleteDirectory(app_path('Strategies'));
    createStrategy(true);

    $pipeline = new ReflectionClass(App\Strategies\PaymentMethod\Pipelines\PaymentMethodPipeline::class);

    expect($pipeline->hasMethod('handle'))->toBeTrue()
        ->and($pipeline->getMethod('handle')->getParameters())->toHaveCount(2);
});
