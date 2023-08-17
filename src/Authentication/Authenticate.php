<?php

namespace Webfox\MYOB\Authentication;

use Exception;
use GuzzleHttp\Client;
use Webfox\MYOB\MYOBFacade;
use Webfox\MYOB\MYOBRequest;
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
        $this->clientId     = config('myob.client_id');
        $this->clientSecret = config('myob.client_secret');
        $this->redirectUrl  = url(config('myob.redirect_uri'));
        $this->grantType    = config('myob.grant_type');
        $this->scope        = config('myob.scope');

        $this->client = $client ?? new Client();
    }

    public function getAuthUrl(): string
    {
        return ('https://secure.myob.com/oauth2/account/authorize?client_id=' . $this->clientId . '&redirect_uri=' . urlencode($this->redirectUrl) . '&response_type=code&scope=' . $this->scope);
    }

    /**
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getToken($refreshToken = null, $code = null)
    {
        if (!$refreshToken && !$code) {
            throw new Exception('No code or refresh token provided');
        } elseif ($refreshToken && $code) {
            throw new Exception('Both code and refresh token provided');
        }

        $response = $this->client->post('https://secure.myob.com/oauth2/v1/authorize', [
            'headers'     => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'redirect_uri'  => $this->redirectUrl,
                'grant_type'    => $refreshToken ? 'refresh_token' : $this->grantType,
                'code'          => $code,
                'refresh_token' => $refreshToken,
            ],
        ]);

        if ($response->getStatusCode() > 299 || $response->getStatusCode() < 200) {
            throw new Exception('Error getting token');
        }

        $body = json_decode($response->getBody()->getContents(), true);

        return $this->updateConfiguration([
            'access_token'  => $body['access_token'],
            'refresh_token' => $body['refresh_token'],
            'scope'         => $body['scope'],
        ]);
    }

    public function checkCompanyFileCredentials($data)
    {
        $config = MYOBFacade::getConfig();

        $request = new MYOBRequest([
            'headers' => [
                'Authorization'     => 'Bearer ' . $config->access_token,
                'x-myobapi-key'     => config('myob.client_id'),
                'x-myobapi-version' => 'v2',
                'x-myobapi-cftoken' => base64_encode($data['username'] . ':' . $data['password']),
                'Accept-Encoding'   => 'gzip,deflate',
            ]
        ]);

        $response = $request->get($data['company_file_uri'] . '/CurrentUser');

        return $response->getStatusCode() > 299 || $response->getStatusCode() < 200 ? false : true;
    }

    public function saveCompanyFileCredentials($data)
    {
        $this->updateConfiguration([
            'company_file_token' => base64_encode($data['username'] . ':' . $data['password']),
            'company_file_id'    => $data['company_file_id'],
            'company_file_name'  => $data['company_file_name'],
            'company_file_uri'   => stripslashes($data['company_file_uri']),
        ]);
    }

    public function disconnect()
    {
        MyobConfiguration::first()->delete();
    }

    protected function updateConfiguration($data)
    {
        return MyobConfiguration::updateOrCreate(['id' => 1], $data);
    }
}