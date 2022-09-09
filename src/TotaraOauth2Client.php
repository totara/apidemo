<?php

namespace Totara\Apidemo;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class TotaraOauth2Client {
    private $client;

    private $siteUrl;

    private $clientId;

    private $clientSecret;

    public function __construct(HttpClientInterface $client, string $siteUrl, string $clientId, string $clientSecret) {
        $this->client = $client;
        $this->siteUrl = $siteUrl;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }
}