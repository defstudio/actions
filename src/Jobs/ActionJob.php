<?php

/** @noinspection PhpUnnecessaryLocalVariableInspection */

/** @noinspection PhpUnhandledExceptionInspection */

namespace DefStudio\Actions\Jobs;

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
    use SerializesModels;

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

        $method = Str::of($property)->studly()->prepend('get');

        return $this->callActionMethod($method);
    }

    public function callActionMethod(string $method, mixed ...$args): mixed
    {
        if (!method_exists($this->actionClass, $method)) {
            return null;
        }

        return $this->action()->$method(...$args);
    }
}
