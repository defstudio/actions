<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace DefStudio\Actions\Concerns;

use DefStudio\Actions\Exceptions\ActionException;
use Illuminate\Support\Collection;
use Mockery\MockInterface;

trait MocksItsBehaviour
{
    public static function mock(mixed ...$mocked): static|MockInterface
    {
        $mock = mock(static::class);

        /** @var Collection<callable(): mixed> $mocked */
        $mocked = collect($mocked)->map(function (mixed $mockedItem) {
            if (is_callable($mockedItem)) {
                return $mockedItem;
            }

            return fn () => $mockedItem;
        });

        if ($mocked->isEmpty()) {
            $mock = $mock->expect();
        } else {
            if ($mocked->count() == 1 && $mocked->keys()->first() == 0) {
                if (!method_exists(static::class, 'handle')) {
                    throw ActionException::undefinedHandleMethod(static::class);
                }

                $mock = $mock->expect(handle: $mocked->first());
            } else {
                /** @phpstan-ignore-next-line  */
                $mock = mock(static::class)->expect(...$mocked->toArray());
            }
        }

        app()->bind(static::class, fn () => $mock);

        return $mock;
    }
}
