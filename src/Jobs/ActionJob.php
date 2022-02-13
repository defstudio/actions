<?php

/** @noinspection PhpDocMissingThrowsInspection */

/** @noinspection PhpUnnecessaryLocalVariableInspection */

/** @noinspection PhpUnhandledExceptionInspection */

namespace DefStudio\Actions\Jobs;

use DefStudio\Actions\Exceptions\ActionException;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Throwable;

/**
 * @template TAction
 */
class ActionJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use Dispatchable;
    use Batchable;
    use SerializesModels {
        __sleep as serializesModels__sleep;
        __wakeup as serializesModels__wakeup;
        __serialize as serializesModels__serialize;
        __unserialize as serializesModels__unserialize;
    }

    /** @var class-string<TAction> */
    protected string $actionClass;

    /** @var array<int, mixed> */
    protected array $parameters;

    public int|null $tries;
    public int|null $timeout;
    public array|null $backoff;

    /**
     * @param class-string<TAction> $actionClass
     */
    public function __construct(
        string $actionClass,
        mixed ...$parameters,
    ) {
        $this->actionClass = $actionClass;
        $this->parameters  = array_values($parameters);

        $this->onQueue($this->getActionProperty('queue')); //@phpstan-ignore-line

        $this->tries   = $this->getActionProperty('tries'); //@phpstan-ignore-line
        $this->timeout = $this->getActionProperty('timeout'); //@phpstan-ignore-line
        $this->backoff = $this->getActionProperty('backoff'); //@phpstan-ignore-line

        $this->callActionMethod('configureJob', $this);
    }

    public function handle(): void
    {
        if (!method_exists($this->actionClass, 'handle')) {
            throw ActionException::undefinedHandleMethod($this->actionClass);
        }

        $this->callActionMethod('handle', ...$this->parameters);
    }

    /**
     * @return TAction
     *
     * @throws BindingResolutionException
     */
    public function action(): mixed
    {
        /** @var TAction $action */
        $action = app()->make($this->actionClass);

        return $action;
    }

    public function getActionProperty(string $property): mixed
    {
        if (property_exists($this->actionClass, $property)) {
            return $this->action()->$property;
        }

        $getPropertyMethod = Str::of($property)->studly()->prepend('get');

        if (method_exists($this->actionClass, $getPropertyMethod)) {
            return $this->callActionMethod($getPropertyMethod);
        }

        return $this->callActionMethod($property);
    }

    public function callActionMethod(string $method, mixed ...$args): mixed
    {
        if (!method_exists($this->actionClass, $method)) {
            return null;
        }

        return $this->action()->$method(...$args);
    }

    public function failed(Throwable $exception): void
    {
        $this->callActionMethod('jobFailed', $exception);
    }

    public function displayName(): string
    {
        /** @var string|null $displayName */
        $displayName = $this->callActionMethod('jobDisplayName', ...$this->parameters);

        return $displayName ?? $this->actionClass;
    }

    public function __sleep(): array
    {
        foreach ($this->parameters as $index => $parameter) {
            $this->parameters[$index] = $this->getSerializedPropertyValue($parameter);
        }

        return $this->serializesModels__sleep();
    }

    public function __wakeup(): void
    {
        $this->serializesModels__wakeup();

        foreach ($this->parameters as $index => $parameter) {
            $this->parameters[$index] = $this->getRestoredPropertyValue($parameter);
        }
    }

    public function __serialize(): array
    {
        foreach ($this->parameters as $index => $parameter) {
            $this->parameters[$index] = $this->getSerializedPropertyValue($parameter);
        }

        return $this->serializesModels__serialize();
    }

    public function __unserialize(array $values): void
    {
        $this->serializesModels__unserialize($values);

        foreach ($this->parameters as $index => $parameter) {
            $this->parameters[$index] = $this->getRestoredPropertyValue($parameter);
        }
    }
}
