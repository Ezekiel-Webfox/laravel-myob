<?php

namespace Webfox\MYOB;

use Exception;
use Webfox\MYOB\Models\MyobConfiguration;
use Webfox\MYOB\Authentication\Authenticate;

class MYOB
{
    protected Authenticate $authenticate;

    public function __construct()
    {
        $this->authenticate = new Authenticate();
    }

    public function authenticate(): Authenticate
    {
        return $this->authenticate;
    }

    public function isConnected(): bool
    {
        return MyobConfiguration::count() > 0;
    }
}