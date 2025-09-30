<?php

namespace Aslnbxrz\MyID;

use Illuminate\Support\Str;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;

class Provider extends AbstractProvider
{
    public const IDENTIFIER = 'MYID';

    protected string $defaultScope = 'openid profile email';

    protected function getAuthUrl($state): string
    {
        // 1) Obtain client_credentials token
        $api = $this->createApiClient();
        $clientToken = $api->getClientCredentialsToken();

        // 2) Create session
        $externalId = $this->parameters['external_id'] ?? (class_exists(Str::class) ? (string)Str::uuid() : uniqid('myid_', true));
        $ipAddress = $this->parameters['ip_address'] ?? null;
        $maxRetries = $this->parameters['max_retries'] ?? null;
        $sessionId = $api->createSession($clientToken, [
            'max_retries' => $maxRetries,
            'external_id' => $externalId,
            'ip_address' => $ipAddress,
        ]);

        // 3) Build redirect to Web SDK
        $query = [
            'session_id' => $sessionId,
            'redirect_uri' => $this->redirectUrl,
        ];

        // Optional identification params provided via ->with([...])
        foreach (['pinfl', 'pass_data', 'birth_date', 'is_resident', 'iframe', 'theme', 'lang'] as $param) {
            if (array_key_exists($param, $this->parameters)) {
                $query[$param] = $this->parameters[$param];
            }
        }

        $base = rtrim($this->getWebBaseUrl(), '/');
        $url = $base . '/?' . http_build_query($query);

        // Socialite requires state param in auth URL; append to query for consistency
        if ($state !== null) {
            $url .= '&state=' . urlencode((string)$state);
        }

        return $url;
    }

    protected function getTokenUrl(): string
    {
        // Not used: token exchange is handled via getAccessTokenResponse (Web SDK flow)
        return rtrim($this->getBaseUrl(), '/') . '/api/v1/oauth2/access-token';
    }

    protected function getUserByToken($token)
    {
        $api = $this->createApiClient();
        return $api->getMe($token);
    }

    protected function getCodeFields($state = null): array
    {
        $fields = parent::getCodeFields($state);
        $fields['response_type'] = 'code';
        $fields['scope'] = $this->getScope();
        return $fields;
    }

    protected function getTokenFields($code): array
    {
        // Not used in our custom exchange below; present for completeness
        $fields = parent::getTokenFields($code);
        $fields['grant_type'] = 'authorization_code';
        return $fields;
    }

    public function getAccessTokenResponse($code): array
    {
        // MyID returns `auth_code` on callback; Socialite passes that into $code when present.
        $api = $this->createApiClient();
        $method = (string)$this->getConfig('method', 'strong');
        $scope = (string)$this->getConfig('user_scope', 'common_data');
        $redirectUri = (string)$this->redirectUrl;
        $token = $api->exchangeAuthCode($code, $redirectUri, $method, $scope);

        return [
            'access_token' => $token,
        ];
    }

    protected function mapUserToObject(array $user): MyIDUser
    {
        $name = $user['name'] ?? trim(implode(' ', array_filter([
            $user['given_name'] ?? null,
            $user['family_name'] ?? null,
        ])));

        return (new MyIDUser())->setRaw($user)->map([
            'id' => $user['sub'] ?? $user['id'] ?? null,
            'name' => $name ?: null,
            'email' => $user['email'] ?? null,
            'avatar' => $user['picture'] ?? null,

            // Custom fields often present in MyID
            'phone' => $user['phone_number'] ?? $user['phone'] ?? null,
            'pinfl' => $user['pinfl'] ?? null,
            'birth_date' => $user['birth_date'] ?? ($user['birthdate'] ?? null),
        ]);
    }

    protected function getBaseUrl(): string
    {
        return $this->getConfig('base_url', 'https://myid.uz');
    }

    protected function getScope(): string
    {
        return (string)($this->getConfig('scope', $this->defaultScope));
    }

    protected function getWebBaseUrl(): string
    {
        return $this->getConfig('web_base_url', 'https://web.myid.uz');
    }

    private function createApiClient(): ApiClient
    {
        return new ApiClient(
            $this->getHttpClient(),
            (string)$this->getBaseUrl(),
            (string)$this->clientId,
            (string)$this->clientSecret,
        );
    }
}


