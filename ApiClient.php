<?php

namespace Aslnbxrz\MyID;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;

class ApiClient
{
    public function __construct(
        private ClientInterface $http,
        private string          $baseUrl,
        private string          $clientId,
        private string          $clientSecret,
    )
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function getClientCredentialsToken(): string
    {
        $response = $this->http->post($this->baseUrl . '/api/v1/oauth2/access-token', [
            RequestOptions::FORM_PARAMS => [
                'grant_type' => 'authorization_code',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ],
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true) ?: [];
        return (string)($data['access_token'] ?? '');
    }

    public function createSession(string $accessToken, array $payload): string
    {
        $response = $this->http->post($this->baseUrl . '/api/v1/web/sessions', [
            RequestOptions::JSON => [
                'max_retries' => $payload['max_retries'] ?? null,
                'external_id' => $payload['external_id'] ?? null,
                'ip_address' => $payload['ip_address'] ?? null,
            ],
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true) ?: [];
        return (string)($data['session_id'] ?? '');
    }

    public function closeSession(string $accessToken, string $sessionId, int $code = 3): void
    {
        $this->http->post($this->baseUrl . '/api/v1/web/sessions/' . rawurlencode($sessionId) . '/client/close', [
            RequestOptions::JSON => ['code' => $code],
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]);
    }

    public function getSessionResult(string $accessToken, string $sessionId): array
    {
        $response = $this->http->post($this->baseUrl . '/api/v1/web/sessions/' . rawurlencode($sessionId) . '/result', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true) ?: [];
    }

    public function exchangeAuthCode(string $code, string $method = 'strong', string $scope = 'common_data'): string
    {
        $response = $this->http->post($this->baseUrl . '/api/v1/oauth2/access-token', [
            RequestOptions::FORM_PARAMS => [
                'grant_type' => 'authorization_code',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $code,
                'method' => $method,
                'scope' => $scope,
            ],
            'headers' => ['Accept' => 'application/json'],
        ]);

        $data = json_decode($response->getBody()->getContents(), true) ?: [];
        return (string)($data['access_token'] ?? '');
    }

    public function getMe(string $userAccessToken): array
    {
        $response = $this->http->get($this->baseUrl . '/api/v1/users/me', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $userAccessToken,
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true) ?: [];
    }
}


