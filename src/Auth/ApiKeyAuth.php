<?php

namespace AlanTiller\DirectusSdk\Auth;

class ApiKeyAuth implements AuthInterface
{
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function authenticate(): string
    {
        return $this->apiKey;
    }

    public function refreshToken(): ?string
    {
        return null; // API Key doesn't support refresh
    }
}