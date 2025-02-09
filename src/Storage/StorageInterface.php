<?php

namespace AlanTiller\DirectusSdk\Storage;

interface StorageInterface
{
    public function set(string $key, $value): void;
    public function get(string $key);
    public function delete(string $key): void;
}