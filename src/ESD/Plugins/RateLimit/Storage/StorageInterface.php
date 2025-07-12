<?php

namespace ESD\Plugins\RateLimit\Storage;

interface StorageInterface
{

    public function __construct(
        string $key,
        int $timeout,
        array $options = []
    );
}