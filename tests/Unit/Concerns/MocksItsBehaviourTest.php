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

    $instance = $class->mock('foo');

    expect($instance->handle('test'))->toBe('foo');
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

it('can spy its behaviour', function () {
    $class = new class() {
        use MocksItsBehaviour;

        public function handle(): string
        {
            return 'was actually called';
        }
    };

    $spy = $class::spy();

    $spy->handle();

    $spy->shouldHaveReceived()
        ->handle();
});

it('can partially mock its behaviour', function () {
    $class = new class() {
        use MocksItsBehaviour;

        public function handle(): string
        {
            throw new ExpectationFailedException('Failed to assert that this class was mocked');
        }

        public function dontMockMe(): string
        {
            return 'I have been executed';
        }
    };

    $action = $class::partial_mock(fn () => 'mocked handle method');

    expect($action->handle())->toBe('mocked handle method');
    expect($action->dontMockMe())->toBe('I have been executed');
});
