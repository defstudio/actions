<?php

namespace DefStudio\Actions\Concerns;

use DefStudio\Actions\Jobs\ActionJob;
use Illuminate\Bus\Dispatcher;
use Illuminate\Foundation\Bus\PendingDispatch;

trait ActsAsJob
{
    public static function job(mixed ...$args): ActionJob
    {
        return new ActionJob(static::class, ...$args);
    }

    public static function dispatch(mixed ...$args): PendingDispatch
    {
        return new PendingDispatch(static::dispatch(...$args));
    }

    public static function dispatchSync(mixed ...$args): mixed
    {
        return app(Dispatcher::class)->dispatchSync(static::job(...$args));
    }
}
