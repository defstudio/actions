<?php

/** @noinspection PhpUnhandledExceptionInspection */

use DefStudio\Actions\Concerns\ActsAsJob;
use DefStudio\Actions\Jobs\ActionJob;
use Illuminate\Bus\PendingBatch;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;

it('creates a job decorator', function () {
    $class = new class() {
        use ActsAsJob;
    };

    expect($class::job())
        ->toBeInstanceOf(ActionJob::class)
        ->action()->toBeInstanceOf($class::class);
});

it('can dispatch as a job', function () {
    $class = new class() {
        use ActsAsJob;
    };

    Queue::fake();

    expect($class::dispatch('test'))
        ->toBeInstanceOf(PendingDispatch::class);

    Queue::assertPushed(ActionJob::class, function (ActionJob $job) use ($class) {
        return $job->action() instanceof $class;
    });
});

it('can dispatch after response', function () {
    $class = new class() {
        use ActsAsJob;
    };

    Bus::fake();

    expect($class::dispatchAfterResponse('test'))
        ->toBeInstanceOf(PendingDispatch::class);

    Bus::assertDispatchedAfterResponse(ActionJob::class, function (ActionJob $job) use ($class) {
        return $job->action() instanceof $class;
    });
});

it('can create a batch', function () {
    $class = new class() {
        use ActsAsJob;

        public function handle($name): string
        {
            return "hello $name";
        }

        public function jobDisplayName($name): string
        {
            return "greet $name";
        }
    };

    Bus::fake();

    $class::batch('fabio', 'luke')->dispatch();

    Bus::assertBatched(function (PendingBatch $batch) {
        expect($batch->jobs->count())->toBe(2);

        expect($batch->jobs->first()->displayName())->toBe('greet fabio');
        expect($batch->jobs->last()->displayName())->toBe('greet luke');

        return true;
    });
});

it('can create a chain', function () {
    $class = new class() {
        use ActsAsJob;

        public function handle($name): string
        {
            return "hello $name";
        }

        public function jobDisplayName($name): string
        {
            return "greet $name";
        }
    };

    Bus::fake();

    $class::chain('fabio', 'luke')->dispatch();

    Bus::assertChained([
        new ActionJob($class::class, 'fabio'),
        new ActionJob($class::class, 'luke'),
    ]);
});
