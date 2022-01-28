<?php

namespace DefStudio\Actions\Jobs;

use DefStudio\Actions\Concerns\ActsAsJob;

class ActionJob
{
    /** @var ActsAsJob */
    public mixed $action;

    /**
     * @param class-string $actionClass
     */
    public function __construct(string $actionClass, mixed ...$args)
    {
        $this->action = app($actionClass);
    }
}
