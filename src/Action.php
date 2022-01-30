<?php

namespace DefStudio\Actions;

use DefStudio\Actions\Concerns\ActsAsAction;
use DefStudio\Actions\Concerns\ActsAsJob;

abstract class Action
{
    use ActsAsAction;
    use ActsAsJob;
}
