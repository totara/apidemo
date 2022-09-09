<?php

namespace Totara\Apidemo;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Totara\Apidemo\Exception\InvalidAccessTokenException;

class TotaraOauth2Client {
    const TOTARA_OAUTH2_TOKEN_PATH = '/totara/oauth2/token.php';
    const TOTARA_API_REQUEST_PATH = '/api/graphql.php';

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

    public function getNewToken() {
        $response = $this->client->request(
            'POST',
            $this->siteUrl . self::TOTARA_OAUTH2_TOKEN_PATH,
            [
                'body' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ]
            ]
        );
        if (200 !== $response->getStatusCode()) {
            throw new \Exception('Error when requesting token');
        }

        $responseJson = $response->getContent();
        $responseData = json_decode($responseJson, true, 512, JSON_THROW_ON_ERROR);

        if (isset($responseData['error'])) {
            throw new \Exception("getNewToken() response contained an error " . $responseData['error'] . ".");
        }

        if (!isset($responseData['access_token']) || empty($responseData['access_token'])) {
            throw new \Exception('getNewToken() response did not contain access token. Check your client id and secret are correct.');
        }

        $_SESSION['access_token'] = $responseData['access_token'];
    }

    public function getToken() {
        return $_SESSION['access_token'] ?? null;
    }

    public function makeAuthenticatedRequest($query, $params = []) {
        try {
            $response = $this->makeRequest($query, $params);
        } catch (InvalidAccessTokenException $e) {
            $this->getNewToken();
            $response = $this->makeRequest($query, $params);
        }

        $responseJson = $response->getContent();
        $responseData = json_decode($responseJson, true, 512, JSON_THROW_ON_ERROR);

        return $responseData;
    }

    private function makeRequest($query, $params = []) {
        $body = json_encode([
            'query' => $query,
            'variables' => $params
        ], JSON_THROW_ON_ERROR|JSON_PRETTY_PRINT|JSON_INVALID_UTF8_IGNORE);

        $response = $this->client->request(
            'POST',
            $this->siteUrl . self::TOTARA_API_REQUEST_PATH,
            [
                'headers' => [
                    'Authorization' => "Bearer {$this->getToken()}",
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'body' => $body
            ]
        );

        $responseJson = $response->getContent();
        $responseData = json_decode($responseJson, true, 512, JSON_THROW_ON_ERROR);

        if (isset($responseData['errors'])) {
            $error = reset($responseData['errors']);
            if (isset($error['debugMessage']) && str_contains($error['debugMessage'], 'Missing, expired or invalid access token')) {
                throw new InvalidAccessTokenException();
            }
        }

        return $response;
    }
}