<?php

/** @noinspection PhpIllegalPsrClassPathInspection */


use DefStudio\Actions\Concerns\InjectsItself;

class TestClass
{
    use InjectsItself;

    public function handle(): string
    {
        return 'test class';
    }
}

;

class TestDouble extends TestClass
{
    public function handle(): string
    {
        return "test double";
    }
}

;

it('can resolve itself from service container', function () {
    expect(TestClass::make())
        ->toBeInstanceOf(TestClass::class);
});

it('can switch itself using service container', function () {
    app()->bind(TestClass::class, fn () => new TestDouble());

    expect(TestClass::make())
        ->toBeInstanceOf(TestDouble::class);
});

it('can run injecting itself from service container', function () {
    expect(TestClass::run())->toBe("test class");

    app()->bind(TestClass::class, fn () => new TestDouble());

    expect(TestClass::run())->toBe("test double");
});
