<?php

/** @noinspection PhpUnnecessaryLocalVariableInspection */

namespace DefStudio\Actions\Concerns;

use DefStudio\Actions\Jobs\ActionJob;
use Illuminate\Bus\PendingBatch;
use Illuminate\Foundation\Bus\PendingChain;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Facades\Bus;

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

    public static function dispatchAfterResponse(mixed ...$args): PendingDispatch
    {
        return self::dispatch(...$args)->afterResponse();
    }

    public static function batch(mixed ...$args): PendingBatch
    {
        return Bus::batch(self::buildJobArray(...$args));
    }

    public static function chain(mixed ...$args): PendingChain
    {
        return Bus::chain(self::buildJobArray(...$args));
    }

    /**
     * @return ActionJob[]
     */
    private static function buildJobArray(mixed ...$args): array
    {
        /** @var ActionJob[] $jobs */
        $jobs = collect($args)
            ->map(function (mixed $jobArgs): ActionJob {
                if (!is_array($jobArgs)) {
                    return static::job($jobArgs);
                }

                $reflection = new \ReflectionMethod(static::class, 'handle');

                if ($reflection->getNumberOfParameters() > 1) {
                    return static::job(...$jobArgs);
                }

                return static::job($jobArgs);
            })->toArray();

        return $jobs;
    }

    public function jobFailed(\Throwable $exception): void
    {
    }
}
