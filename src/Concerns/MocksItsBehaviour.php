<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace DefStudio\Actions\Concerns;

use DefStudio\Actions\Exceptions\ActionException;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\MockInterface;

trait MocksItsBehaviour
{
    public static function mock(mixed ...$mocked): static|MockInterface|Mockery\LegacyMockInterface
    {
        /** @var Collection<array-key, callable(): mixed> $mocked */
        $mocked = collect($mocked)->map(function (mixed $mockedItem) {
            if (is_callable($mockedItem)) {
                return $mockedItem;
            }

            return fn () => $mockedItem;
        });

        $mock = \Mockery::mock(static::class);

        if ($mocked->isNotEmpty()) {
            if ($mocked->count() == 1 && $mocked->keys()->first() == 0) {
                if (!method_exists(static::class, 'handle')) {
                    throw ActionException::undefinedHandleMethod(static::class);
                }

                $mock->shouldReceive('handle')->andReturnUsing($mocked->first());
            } else {
                foreach ($mocked as $method => $callback) {
                    $mock->shouldReceive($method)->andReturnUsing($callback);
                }
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
