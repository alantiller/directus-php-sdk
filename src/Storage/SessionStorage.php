<?php

namespace AlanTiller\DirectusSdk\Storage;

class SessionStorage implements StorageInterface
{
    private string $prefix;

    public function __construct(string $prefix = '')
    {
        $this->prefix = $prefix;
    }

    public function set(string $key, $value): void
    {
        $_SESSION[$this->prefix . $key] = $value;
    }

    public function get(string $key)
    {
        return $_SESSION[$this->prefix . $key] ?? null;
    }

    public function delete(string $key): void
    {
        unset($_SESSION[$this->prefix . $key]);
    }
}