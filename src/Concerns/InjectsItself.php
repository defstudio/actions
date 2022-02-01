<?php

/** @noinspection PhpMethodParametersCountMismatchInspection */

/** @noinspection PhpUnhandledExceptionInspection */

namespace DefStudio\Actions\Concerns;

use DefStudio\Actions\Exceptions\ActionException;

trait InjectsItself
{
    public static function make(array $parameters = []): static
    {
        return app(static::class, $parameters);
    }

    public static function run(mixed ...$parameters): mixed
    {
        $instance = static::make();

        if (!method_exists(static::class, 'handle')) {
            throw ActionException::undefinedHandleMethod(static::class);
        }

        /* @phpstan-ignore-next-line */
        return $instance->handle(...$parameters);
    }

    /**
     * @return array<int|string, mixed>
     *
     * @throws ActionException
     */
    public static function runMany(mixed ...$parameters): array
    {
        if (!method_exists(static::class, 'handle')) {
            throw ActionException::undefinedHandleMethod(static::class);
        }

        return collect($parameters)
            ->map(function (mixed $runParams) {
                if (!is_array($runParams)) {
                    return static::run($runParams);
                }

                $reflection = new \ReflectionMethod(static::class, 'handle');

                if ($reflection->getNumberOfParameters() > 1) {
                    return static::run(...$runParams);
                }

                return static::run($runParams);
            })->toArray();
    }
}
