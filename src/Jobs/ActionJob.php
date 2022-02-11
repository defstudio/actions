<?php /** @noinspection PhpDocMissingThrowsInspection */

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
        __sleep as __originalSleep;
        __wakeup as __originalWakeup;
        __serialize as __originalSerialize;
        __unserialize as __originalUnserialize;
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
        if (!method_exists($actionClass, 'handle')) {
            throw ActionException::undefinedHandleMethod($actionClass);
        }

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

    public function failed(\Throwable $exception): void
    {
        $this->callActionMethod('jobFailed', $exception);
    }

    public function displayName(): string
    {
        /** @var string|null $displayName */
        $displayName = $this->callActionMethod('jobDisplayName', ...$this->parameters);

        return $displayName ?? $this->actionClass;
    }

    public function __sleep()
    {
        $this->parameters = collect($this->parameters)
            ->map(fn(mixed $parameter) => $this->getSerializedPropertyValue($parameter))
            ->toArray();

        return $this->__originalSleep();
    }

    public function __wakeup()
    {
        $this->__originalWakeup();

        $this->parameters = collect($this->parameters)
            ->map(fn(mixed $parameter) => $this->getRestoredPropertyValue($parameter))
            ->toArray();
    }

    public function __serialize()
    {
        $this->parameters = collect($this->parameters)
            ->map(fn(mixed $parameter) => $this->getSerializedPropertyValue($parameter))
            ->toArray();

        return $this->__originalSerialize();
    }

    public function __unserialize(array $values)
    {
        $this->__originalUnserialize($values);

        $this->parameters = collect($this->parameters)
            ->map(fn(mixed $parameter) => $this->getRestoredPropertyValue($parameter))
            ->toArray();
    }
}
