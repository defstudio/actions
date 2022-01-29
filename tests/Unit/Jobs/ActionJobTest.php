<?php

use DefStudio\Actions\Concerns\ActsAsJob;
use DefStudio\Actions\Concerns\MocksItsBehaviour;
use DefStudio\Actions\Jobs\ActionJob;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\ExpectationFailedException;

test('queue can be chosen', function () {
    $class = new class() {
        use ActsAsJob;

        public string $queue = 'test_queue';
    };

    Queue::fake();

    $job = new ActionJob($class::class);

    dispatch($job);

    Queue::assertPushedOn('test_queue', ActionJob::class);
});

test('queue can be chosen with a closure', function () {
    $class = new class() {
        use ActsAsJob;

        public function getQueue(): string
        {
            return 'test_queue';
        }
    };

    Queue::fake();

    $job = new ActionJob($class::class);

    dispatch($job);

    Queue::assertPushedOn('test_queue', ActionJob::class);
});

test('tries can be chosen', function () {
    $class = new class() {
        use ActsAsJob;

        public int $tries = 42;
    };

    $job = new ActionJob($class::class);

    expect($job->tries)->toBe(42);
});

test('tries can be chosen with a closure', function () {
    $class = new class() {
        use ActsAsJob;

        public function getTries(): int
        {
            return 42;
        }
    };

    $job = new ActionJob($class::class);

    expect($job->tries)->toBe(42);
});

test('timeout can be chosen', function () {
    $class = new class() {
        use ActsAsJob;

        public int $timeout = 150;
    };

    $job = new ActionJob($class::class);

    expect($job->timeout)->toBe(150);
})->only();

test('timeout can be chosen with a closure', function () {
    $class = new class() {
        use ActsAsJob;

        public function getTimeout(): int
        {
            return 150;
        }
    };

    $job = new ActionJob($class::class);

    expect($job->timeout)->toBe(150);
})->only();

test('backoff can be chosen', function () {
    $class = new class() {
        use ActsAsJob;

        public array $backoff = [10, 100, 1000];
    };

    $job = new ActionJob($class::class);

    expect($job->backoff)->toMatchArray([10, 100, 1000]);
})->only();

test('backoff can be chosen with a closure', function () {
    $class = new class() {
        use ActsAsJob;

        public function getBackoff(): array
        {
            return [10, 100, 1000];
        }
    };

    $job = new ActionJob($class::class);

    expect($job->backoff)->toMatchArray([10, 100, 1000]);
})->only();

test('job can be configured from action', function () {
    $class = new class() {
        use ActsAsJob;

        public function configureJob(ActionJob $job)
        {
            $job->onQueue('test');
            $job->tries = 3;
            $job->timeout = 15;
            $job->backoff = [1, 3, 8];
        }
    };

    $job = new ActionJob($class::class);

    expect($job)
        ->tries->toBe(3)
        ->timeout->toBe(15)
        ->backoff->toMatchArray([1, 3, 8]);

    Queue::fake();

    dispatch($job);

    Queue::assertPushedOn('test', ActionJob::class);
})->only();

test('action handle method is called', function () {
    $class = new class() {
        use ActsAsJob;
        use MocksItsBehaviour;

        public function handle(): void
        {
            throw new ExpectationFailedException('This method should not be called');
        }
    };

    $class::mock(fn () => null);

    $class::dispatch();
});
