<?php

/** @noinspection PhpUnhandledExceptionInspection */

use DefStudio\Actions\Concerns\ActsAsAction;
use DefStudio\Actions\Concerns\InjectsItself;
use DefStudio\Actions\Concerns\MocksItsBehaviour;

it('uses right concerns', function () {
    $reflection = new ReflectionClass(ActsAsAction::class);

    expect($reflection->getTraitNames())
        ->toMatchArray([
            InjectsItself::class,
            MocksItsBehaviour::class,
        ]);
});
