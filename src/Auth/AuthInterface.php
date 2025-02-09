<?php

namespace AlanTiller\DirectusSdk\Auth;

interface AuthInterface
{
    public function authenticate(): string; // Returns the token
    public function refreshToken(): ?string; // Returns the new token or null if not supported
}