<?php

use DefStudio\Actions\Concerns\ActsAsJob;
use DefStudio\Actions\Concerns\MocksItsBehaviour;
use DefStudio\Actions\Exceptions\ActionException;
use DefStudio\Actions\Jobs\ActionJob;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\ExpectationFailedException;

test('queue can be chosen', function () {
    $class = new class {
        use ActsAsJob;

        public string $queue = 'test_queue';
    };

    Queue::fake();

    $job = new ActionJob($class::class);

    dispatch($job);

    Queue::assertPushedOn('test_queue', ActionJob::class);
});

test('queue can be chosen with a closure', function () {
    $class = new class {
        use ActsAsJob;

        public function queue(): string
        {
            return 'test_queue';
        }
    };

    Queue::fake();

    $job = new ActionJob($class::class);

    dispatch($job);

    Queue::assertPushedOn('test_queue', ActionJob::class);
});

test('queue can be chosen with a getClosure', function () {
    $class = new class {
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
    $class = new class {
        use ActsAsJob;

        public int $tries = 42;
    };

    $job = new ActionJob($class::class);

    expect($job->tries)->toBe(42);
});

test('tries can be chosen with a closure', function () {
    $class = new class {
        use ActsAsJob;

        public function tries(): int
        {
            return 42;
        }
    };

    $job = new ActionJob($class::class);

    expect($job->tries)->toBe(42);
});

test('tries can be chosen with a getClosure', function () {
    $class = new class {
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
    $class = new class {
        use ActsAsJob;

        public int $timeout = 150;
    };

    $job = new ActionJob($class::class);

    expect($job->timeout)->toBe(150);
});

test('timeout can be chosen with a closure', function () {
    $class = new class {
        use ActsAsJob;

        public function timeout(): int
        {
            return 150;
        }
    };

    $job = new ActionJob($class::class);

    expect($job->timeout)->toBe(150);
});

test('timeout can be chosen with a getClosure', function () {
    $class = new class {
        use ActsAsJob;

        public function getTimeout(): int
        {
            return 150;
        }
    };

    $job = new ActionJob($class::class);

    expect($job->timeout)->toBe(150);
});

test('backoff can be chosen', function () {
    $class = new class {
        use ActsAsJob;

        public array $backoff = [10, 100, 1000];
    };

    $job = new ActionJob($class::class);

    expect($job->backoff)->toMatchArray([10, 100, 1000]);
});

test('backoff can be chosen with a closure', function () {
    $class = new class {
        use ActsAsJob;

        public function backoff(): array
        {
            return [10, 100, 1000];
        }
    };

    $job = new ActionJob($class::class);

    expect($job->backoff)->toMatchArray([10, 100, 1000]);
});

test('backoff can be chosen with a getClosure', function () {
    $class = new class {
        use ActsAsJob;

        public function getBackoff(): array
        {
            return [10, 100, 1000];
        }
    };

    $job = new ActionJob($class::class);

    expect($job->backoff)->toMatchArray([10, 100, 1000]);
});

test('job can be configured from action', function () {
    $class = new class {
        use ActsAsJob;

        public function configureJob(ActionJob $job)
        {
            $job->onQueue('test');
            $job->tries   = 3;
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
});

test('action handle method is called', function () {
    $class = new class {
        use ActsAsJob;
        use MocksItsBehaviour;

        public function handle(): void
        {
            throw new ExpectationFailedException('This method should not be called');
        }
    };

    $class::mock(fn () => null);

    expect(fn () => $class::dispatch())->not()->toThrow(ExpectationFailedException::class);

    $class::dispatch();
});

test('action handle method is required', function () {
    $class = new class {
        use ActsAsJob;
    };

    $class::dispatch();
})->throws(ActionException::class);

test('actions can handle failures', function () {
    $class = new class {
        use ActsAsJob;

        public static bool $handled = false;

        public function jobFailed($exception): void
        {
            self::$handled = true;
        }
    };

    $jobClass = new ActionJob($class::class);
    $jobClass->failed(new Exception('test'));

    expect($class::$handled)->toBeTrue();
});
