<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace DefStudio\Actions\Concerns;

use DefStudio\Actions\Exceptions\ActionException;
use Mockery\MockInterface;

trait MocksItsBehaviour
{
    public static function mock(callable ...$methods): static|MockInterface
    {
        $mock = mock(static::class);

        if (count($methods) == 1 && array_key_first($methods) == 0) {
            if (!method_exists(static::class, 'handle')) {
                throw ActionException::undefinedHandleMethod(static::class);
            }

            $mock = $mock->expect(handle: $methods[0]);
        } else {
            $mock = mock(static::class)->expect(...$methods);
        }

        app()->bind(static::class, fn () => $mock);

        return $mock;
    }
}
