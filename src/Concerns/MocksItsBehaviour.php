<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace DefStudio\Actions\Concerns;

use DefStudio\Actions\Exceptions\ActionException;
use Mockery\MockInterface;

trait MocksItsBehaviour
{
    public static function mock(mixed ...$mocked): static|MockInterface
    {
        $mock = mock(static::class);

        $mocked = collect($mocked)->map(function (mixed $mockedItem) {
            if (is_callable($mockedItem)) {
                return $mockedItem;
            }

            return fn () => $mockedItem;
        })->toArray();

        if (count($mocked) == 1 && array_key_first($mocked) == 0) {
            if (!method_exists(static::class, 'handle')) {
                throw ActionException::undefinedHandleMethod(static::class);
            }

            $mock = $mock->expect(handle: $mocked[0]);
        } else {
            $mock = mock(static::class)->expect(...$mocked);
        }

        app()->bind(static::class, fn () => $mock);

        return $mock;
    }
}
