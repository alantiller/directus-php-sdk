<?php

namespace AlanTiller\DirectusSdk\Auth;

class StaticTokenAuth implements AuthInterface
{
    private string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function authenticate(): string
    {
        return $this->token;
    }

    public function refreshToken(): ?string
    {
        return null; // Static token doesn't support refresh
    }
}