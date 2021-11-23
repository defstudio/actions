<?php

namespace DefStudio\Actions\Concerns;

use DefStudio\Actions\Exceptions\ActionException;

trait InjectsItself
{
    public static function make(): static
    {
        return app(static::class);
    }

    public static function run(mixed $arguments): mixed
    {
        $instance = static::make();

        if (!method_exists(static::class, 'handle')) {
            throw ActionException::undefinedHandleMethod(static::class);
        }

        return $instance->handle(...$arguments);
    }
}
