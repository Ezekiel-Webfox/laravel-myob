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

    public function getConfig()
    {
        $config = MyobConfiguration::first();

        if (!$config) {
            throw new Exception('No MYOB configuration found');
        }

        if ($config->expires_at->isPast()) {
            $config = $this->authenticate->getToken($config->refresh_token);
        }

        return $config;
    }
    protected function getRequest()
    {
        $config = $this->getConfig();

        return new MYOBRequest([
            'Authorization'     => 'Bearer ' . $config->access_token,
            'x-myobapi-key'     => config('myob.client_id'),
            'x-myobapi-version' => 'v2',
            'x-myobapi-cftoken' => $config->company_file_token,
            'Accept-Encoding'   => 'gzip,deflate',
        ]);
    }

    public function get($endpoint, array $options = [])
    {
        return $this->getRequest()->get($endpoint, $options);
    }

    public function post($endpoint, $data)
    {
        return $this->getRequest()->post($endpoint, $data);
    }

    public function isConnected(): bool
    {
        return MyobConfiguration::count() > 0;
    }
}