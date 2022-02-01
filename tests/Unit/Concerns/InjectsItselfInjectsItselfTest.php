<?php

/** @noinspection PhpMultipleClassesDeclarationsInOneFile */
/* @noinspection PhpUnhandledExceptionInspection */

/* @noinspection PhpIllegalPsrClassPathInspection */

use DefStudio\Actions\Concerns\InjectsItself;
use DefStudio\Actions\Exceptions\ActionException;

class InjectsItselfTestClass
{
    use InjectsItself;

    public function handle(): string
    {
        return 'test class';
    }
}

class InjectsItselfTestDouble extends InjectsItselfTestClass
{
    public function handle(): string
    {
        return 'test double';
    }
}

it('can resolve itself from service container', function () {
    expect(InjectsItselfTestClass::make())
        ->toBeInstanceOf(InjectsItselfTestClass::class);
});

it('can switch itself using service container', function () {
    app()->bind(InjectsItselfTestClass::class, fn () => new InjectsItselfTestDouble());

    expect(InjectsItselfTestClass::make())
        ->toBeInstanceOf(InjectsItselfTestDouble::class);
});

it('can run injecting itself from service container', function () {
    expect(InjectsItselfTestClass::run())->toBe('test class');

    app()->bind(InjectsItselfTestClass::class, fn () => new InjectsItselfTestDouble());

    expect(InjectsItselfTestClass::run())->toBe('test double');
});

it('requires and handle method to run injecting from service container', function () {
    $class = new class() {
        use InjectsItself;

        public function execute(): string
        {
            return 'executed';
        }
    };

    $class::run();
})->throws(ActionException::class);

it('can run itself multiple times', function ($class, $parameters, $result) {
    expect($class::runMany(...$parameters))->toMatchArray($result);
})->with([
    'single parameter' => [
        'class' => new class() {
            use InjectsItself;

            public function handle(string $name): string
            {
                return "hello $name";
            }
        },
        'parameters' => ['fabio', 'nuno', 'taylor'],
        'result'     => [
            'hello fabio',
            'hello nuno',
            'hello taylor',
        ],
    ],
    'multiple parameters' => [
        'class' => new class() {
            use InjectsItself;

            public function handle(array $names): string
            {
                return "hello {$names[0]} and {$names[1]}";
            }
        },
        'parameters' => [['fabio', 'oliver'], ['nuno', 'luke'], ['freek', 'francisco']],
        'result'     => [
            'hello fabio and oliver',
            'hello nuno and luke',
            'hello freek and francisco',
        ],
    ],
    'named parameters' => [
        'class' => new class() {
            use InjectsItself;

            public function handle(int $a = 1, int $b = 2): float
            {
                return $a / $b;
            }
        },
        'parameters' => [['a' => 4, 'b' => 2], ['b' => 4]],
        'result'     => [2, 0.25],
    ],
    'detault parameters' => [
        'class' => new class() {
            use InjectsItself;

            public function handle(int $a = 1, int $b = 2): float
            {
                return $a / $b;
            }
        },
        'parameters' => [[], [2]],
        'result'     => [0.5, 1],
    ],
]);
