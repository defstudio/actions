<?php

namespace DefStudio\Actions\Exceptions;

class ActionException extends \Exception
{
    /**
     * @param class-string $action_class
     */
    public static function undefinedHandleMethod(string $action_class): ActionException
    {
        return new self(sprintf('There is no [handle] method in %s action', $action_class));
    }
}
