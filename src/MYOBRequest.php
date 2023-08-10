<?php

namespace Webfox\MYOB;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class MYOBRequest
{
    protected ?ClientInterface $client;
    public function __construct(
        protected array $config = [],
    )
    {
        $this->client = new Client($config);
    }

    public function post(string $url, array $options = []): \Psr\Http\Message\ResponseInterface
    {
        $body = $options['body'] ?? json_decode($options);
        $headers = empty($this->config['headers']) ? $options['headers'] : $this->config['headers'];

        return $this->client->post("$url?returnBody=true", [
            'headers' => $headers,
            'body' => $body,
        ]);
    }

    public function get(string $url, array $options = []): \Psr\Http\Message\ResponseInterface
    {
        return $this->client->get($url, $options);
    }
}