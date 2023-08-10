<?php

namespace Webfox\MYOB\Authentication;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Webfox\MYOB\Models\MyobConfiguration;

class Authenticate
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $redirectUrl;
    protected string $grantType;
    protected string $scope;

    protected ClientInterface $client;

    public function __construct(ClientInterface $client = null)
    {
        $this->clientId = config('myob.client_id');
        $this->clientSecret = config('myob.client_secret');
        $this->redirectUrl = config('myob.redirect_url');
        $this->grantType = config('myob.grant_type');
        $this->scope = config('myob.scope');

        $this->client = $client ?? new Client();
    }

    public function getAuthUrl(): string
    {
        return ('https://secure.myob.com/oauth2/account/authorize?client_id=' . $this->clientId . '&redirect_uri=' . $this->redirectUrl . '&response_type=code&scope=' . $this->scope);
    }

    /**
     * @throws \Exception
     */
    public function getToken($refreshToken = null, $code = null)
    {
        if (!$refreshToken && !$code) {
            throw new Exception('No code or refresh token provided');
        } elseif ($refreshToken && $code) {
            throw new Exception('Both code and refresh token provided');
        }

        $response = $this->client->post('https://secure.myob.com/oauth2/v1/authorize', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => $refreshToken ? 'refresh_token' : $this->grantType,
                'code' => $code,
                'refresh_token' => $refreshToken,
            ],
        ]);

        if ($response->getStatusCode() > 299 || $response->getStatusCode() < 200) {
            throw new Exception('Error getting token');
        }

        $body = json_decode($response->getBody()->getContents(), true);

        return  MyobConfiguration::updateOrCreate(['id' => 1], [
            'access_token' => $body['access_token'],
            'refresh_token' => $body['refresh_token'],
            'scope' => $body['scope'],
        ]);
    }
}