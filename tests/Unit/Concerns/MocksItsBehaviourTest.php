<?php

use DefStudio\Actions\Concerns\MocksItsBehaviour;
use DefStudio\Actions\Exceptions\ActionException;
use PHPUnit\Framework\ExpectationFailedException;

it('can quickly mock its behaviour', function () {
    $class = new class() {
        use MocksItsBehaviour;

        public function handle(): string
        {
            throw new ExpectationFailedException('Failed to assert that this class was mocked');
        }
    };

    $instance = $class->mock(function () {
        return 'mocked successfully';
    });

    expect($instance->handle())->toBe('mocked successfully');
});

it('can quickly mock its return value', function () {
    $class = new class() {
        use MocksItsBehaviour;

        public function handle($param): string
        {
            throw new ExpectationFailedException('Failed to assert that this class was mocked with param' . $param);
        }
    };

    $instance = $class->mock(33);

    expect($instance->handle('test'))->toBe(33);
});

it('and handle method is required for quick mock', function () {
    $class = new class() {
        use MocksItsBehaviour;

        public function execute(): string
        {
            throw new ExpectationFailedException('Failed to assert that this class was mocked');
        }
    };

    $class->mock(function () {
        return 'mocked successfully';
    });
})->throws(ActionException::class);

it('can quickly mock all its methods', function () {
    $class = new class() {
        use MocksItsBehaviour;

        public function handle(): string
        {
            throw new ExpectationFailedException('Failed to assert this method was mocked');
        }

        public function execute(): string
        {
            throw new ExpectationFailedException('Failed to assert this method was mocked');
        }
    };

    $instance = $class->mock(
        handle : fn () => '"handle" mocked successfully',
        execute: fn () => '"execute" mocked successfully',
    );

    expect($instance->handle())->toBe('"handle" mocked successfully');
    expect($instance->execute())->toBe('"execute" mocked successfully');
});
