<?php

declare(strict_types=1);

namespace App\Support\LegacyForm;

use Illuminate\Support\Facades\Facade;

class FormFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'legacyform';
    }
}
