<?php

namespace Webfox\MYOB;

use Illuminate\Support\Facades\Facade;

class MYOBFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Webfox\MYOB\MYOB::class;
    }
}