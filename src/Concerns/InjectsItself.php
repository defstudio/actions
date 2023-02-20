<?php

/** @noinspection PhpMethodParametersCountMismatchInspection */

/** @noinspection PhpUnhandledExceptionInspection */

namespace DefStudio\Actions\Concerns;

use DefStudio\Actions\Exceptions\ActionException;
use \ReflectionClass;
use \ReflectionParameter;

trait InjectsItself
{
    public static function make(mixed ...$parameters): static
    {
        if (empty($parameters)) {
            return app(static::class);
        }

        if (!is_array($parameters[0])) {
            $reflection = new ReflectionClass(static::class);

            if ($reflection->getConstructor()->getNumberOfRequiredParameters() <= count($parameters)) {
                $parametersNames = collect($reflection->getConstructor()->getParameters())
                    ->map(fn (ReflectionParameter $parameter) => $parameter->getName())
                    ->values();

                $newParameters = [];
                foreach (array_values($parameters) as $index => $parameter) {
                    $newParameters[$parametersNames->get($index)] = $parameter;
                }

                $parameters = $newParameters;
            }
        }


        return app(static::class, $parameters);
    }

    public static function run(mixed ...$args): mixed
    {
        $instance = static::make();

        if (!method_exists(static::class, 'handle')) {
            throw ActionException::undefinedHandleMethod(static::class);
        }

        /* @phpstan-ignore-next-line */
        return $instance->handle(...$args);
    }

    public static function runMany(mixed ...$args): array
    {
        if (!method_exists(static::class, 'handle')) {
            throw ActionException::undefinedHandleMethod(static::class);
        }

        return collect($args)
            ->map(function (mixed $runArgs) {
                if (!is_array($runArgs)) {
                    return static::run($runArgs);
                }

                $reflection = new \ReflectionMethod(static::class, 'handle');

                if ($reflection->getNumberOfParameters() > 1) {
                    return static::run(...$runArgs);
                }

                return static::run($runArgs);
            })->toArray();
    }
}
