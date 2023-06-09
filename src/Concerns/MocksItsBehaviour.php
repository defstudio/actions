<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace DefStudio\Actions\Concerns;

use DefStudio\Actions\Exceptions\ActionException;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\MockInterface;
use Pest\Mock\Mock;

trait MocksItsBehaviour
{
    public static function mock(mixed ...$mocked): static|MockInterface|Mockery\LegacyMockInterface
    {
        $mock = new Mock(static::class);

        /** @var Collection<array-key, callable(): mixed> $mocked */
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

                /** @phpstan-ignore-next-line  */
                $mock = $mock->expect(handle: $mocked->first());
            } else {
                /** @phpstan-ignore-next-line  */
                $mock = (new Mock(static::class))->expect(...$mocked->toArray());
            }
        }

        app()->bind(static::class, fn () => $mock);

        return $mock;
    }

    public static function partial_mock(mixed ...$mocked): static|MockInterface|Mockery\LegacyMockInterface
    {
        /* @phpstan-ignore-next-line */
        return self::mock(...$mocked)->makePartial();
    }

    public static function spy(): static|MockInterface|Mockery\LegacyMockInterface
    {
        $spy = \Mockery::spy(static::class);

        app()->bind(static::class, fn () => $spy);

        return $spy;
    }
}
