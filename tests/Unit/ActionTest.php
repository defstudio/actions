<?php

use DefStudio\Actions\Action;
use DefStudio\Actions\Concerns\ActsAsAction;

it('uses the right concerns', function () {
    $reflection = new ReflectionClass(Action::class);

    expect($reflection->getTraitNames())
        ->toMatchArray([
            ActsAsAction::class,
        ]);
});
