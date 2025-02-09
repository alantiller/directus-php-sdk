<?php

namespace AlanTiller\DirectusSdk\Storage;

class CookieStorage implements StorageInterface
{
    private string $prefix;
    private string $domain;

    public function __construct(string $prefix = '', string $domain = '/')
    {
        $this->prefix = $prefix;
        $this->domain = $domain;
    }

    public function set(string $key, $value): void
    {
        setcookie($this->prefix . $key, $value, time() + 604800, '/', $this->domain);
    }

    public function get(string $key)
    {
        return $_COOKIE[$this->prefix . $key] ?? null;
    }

    public function delete(string $key): void
    {
        setcookie($this->prefix . $key, '', time() - 1, '/', $this->domain);
    }
}