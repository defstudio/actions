<?php

/** @noinspection PhpUnhandledExceptionInspection */

use DefStudio\Actions\Concerns\ActsAsJob;
use DefStudio\Actions\Jobs\ActionJob;
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

it('dispatches action', function () {
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

it('dispatches action after response', function () {
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
