<?php

namespace DefStudio\Actions\Concerns;

use DefStudio\Actions\Jobs\ActionJob;
use Illuminate\Foundation\Bus\PendingDispatch;

trait ActsAsJob
{
    public static function job(mixed ...$args): ActionJob
    {
        return new ActionJob(static::class, ...$args);
    }

    public static function dispatch(mixed ...$args): PendingDispatch
    {
        return new PendingDispatch(static::job(...$args));
    }

    public static function dispatchSync(mixed ...$args): mixed
    {
        return dispatch_sync(static::job(...$args));
    }

    public static function dispatchAfterResponse(mixed ...$args): PendingDispatch
    {
        return self::dispatch(...$args)->afterResponse();
    }
}
