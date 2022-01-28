<?php


use DefStudio\Actions\Concerns\MocksItsBehaviour;
use PHPUnit\Framework\ExpectationFailedException;

it('can mocks its behaviour', function () {
    $class = new class {
        use MocksItsBehaviour;

        public function handle(): string
        {
            throw new ExpectationFailedException("Failed to assert that this class was mocked");
        }
    };

    $class->mock(function () {
        return "mocked successfully";
    });


    $instance = app($class::class);

    expect($instance->handle())->toBe("mocked successfully");
});
