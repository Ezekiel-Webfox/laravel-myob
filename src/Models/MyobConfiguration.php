<?php

namespace Webfox\MYOB\Models;

use Webfox\MYOB\MYOBFacade;
use Webfox\MYOB\MYOBRequest;
use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MyobConfiguration extends Model
{
    use HasFactory;

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected $appends = [
        'has_configured_company_file',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
        'company_file_token',
    ];

    public static function booted(): void
    {
        static::retrieved(function (MyobConfiguration $model) {
            if ($model->expires_at->isPast()) {
                $model->refreshAccessToken();
            }
        });

        static::saving(function (MyobConfiguration $model) {
            $model->expires_at = now()->addSeconds(900);
        });
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function createFromAuthCode($code, ?array $attributes = []): MyobConfiguration
    {
        return static::create([...MYOBFacade::authenticate()->getTokens(code: $code), ...$attributes]);
    }

    public function hasConfiguredCompanyFile(): Attribute
    {
        return Attribute::make(
            get: function ($val, $attributes) {
                return $attributes['company_file_id']
                    && $attributes['company_file_name']
                    && $attributes['company_file_uri'];
            }
        );
    }

    public function getRequest(): MYOBRequest
    {
        $headers = [
            'Authorization'     => 'Bearer ' . $this->access_token,
            'x-myobapi-key'     => config('myob.client_id'),
            'x-myobapi-version' => 'v2',
            'Accept-Encoding'   => 'gzip,deflate',
        ];

        if ($this->company_file_token) {
            $headers['x-myobapi-cftoken'] = $this->company_file_token;
        }

        return new MYOBRequest([
            'headers' => $headers,
        ]);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function refreshAccessToken(): bool
    {
        return $this->fill(MYOBFacade::authenticate()->getTokens(refreshToken: $this->refresh_token))->save();
    }

    public function saveCompanyFileCredentials($data): bool
    {
        $config = [
            'company_file_id'   => $data['company_file_id'],
            'company_file_name' => $data['company_file_name'],
            'company_file_uri'  => stripslashes($data['company_file_uri']),
        ];

        if (isset($data['username']) && isset($data['password'])) {
            $config['company_file_token'] = base64_encode($data['username'] . ':' . $data['password']);
        }

        return $this->fill($config)->save();
    }

    public function checkCompanyFileCredentials($data): bool
    {
        $requestHeaders = [
            'Authorization'     => 'Bearer ' . $this->access_token,
            'x-myobapi-key'     => config('myob.client_id'),
            'x-myobapi-version' => 'v2',
            'Accept-Encoding'   => 'gzip,deflate',
        ];

        if (isset($data['username']) && isset($data['password'])) {
            $requestHeaders['x-myobapi-cftoken'] = base64_encode($data['username'] . ':' . $data['password']);
        }

        $request = new MYOBRequest([
            'headers' => $requestHeaders,
        ]);

        try {
            $response = $request->get($data['company_file_uri'] . '/CurrentUser');
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                if ($response->getStatusCode() === 401) {
                    return false;
                }
            }
            throw $e;
        }

        return !($response->getStatusCode() > 299 || $response->getStatusCode() < 200);
    }
}
