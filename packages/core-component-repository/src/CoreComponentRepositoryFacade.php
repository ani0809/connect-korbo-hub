<?php

namespace MehediIitdu\CoreComponentRepository;

use Illuminate\Support\Facades\Facade;

class CoreComponentRepositoryFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'core-component-repository';
    }
}
