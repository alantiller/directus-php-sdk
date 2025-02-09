<?php

namespace AlanTiller\DirectusSdk\Auth;

use GuzzleHttp\Client;
use AlanTiller\DirectusSdk\Exceptions\AuthenticationException;

class UserPasswordAuth implements AuthInterface
{
    private string $baseUrl;
    private string $username;
    private string $password;
    private ?string $accessToken = null;
    private ?string $refreshToken = null;

    public function __construct(string $baseUrl, string $username, string $password)
    {
        $this->baseUrl = $baseUrl;
        $this->username = $username;
        $this->password = $password;
    }

    public function authenticate(): string
    {
        $client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        try {
            $response = $client->post('/auth/login', [
                'json' => [
                    'email' => $this->username,
                    'password' => $this->password,
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            if (isset($data['data']['access_token']) && isset($data['data']['refresh_token'])) {
                $this->accessToken = $data['data']['access_token'];
                $this->refreshToken = $data['data']['refresh_token'];
                return $this->accessToken;
            } else {
                throw new AuthenticationException('Invalid credentials');
            }
        } catch (\Exception $e) {
            throw new AuthenticationException('Authentication failed: ' . $e->getMessage());
        }
    }

    public function refreshToken(): ?string
    {
        if (!$this->refreshToken) {
            return null;
        }

        $client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        try {
            $response = $client->post('/auth/refresh', [
                'json' => [
                    'refresh_token' => $this->refreshToken,
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            if (isset($data['data']['access_token']) && isset($data['data']['refresh_token'])) {
                $this->accessToken = $data['data']['access_token'];
                $this->refreshToken = $data['data']['refresh_token'];
                return $this->accessToken;
            } else {
                return null;
            }
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }
}