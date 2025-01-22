<?php

namespace ESD\Coordinator;

class Factory
{
    /**
     * Block the current coroutine until the specified identifier is resumed.
     * Alias of `CoordinatorManager::until($identifier)->yield($timeout)`.
     */
    public static function block(float $timeout = -1, string $identifier = Constants::WORKER_EXIT): bool
    {
        return CoordinatorManager::until($identifier)->yield($timeout);
    }

    /**
     * Resume the coroutine that is blocked by the specified identifier.
     * Alias of `CoordinatorManager::until($identifier)->resume()`.
     */
    public static function resume(string $identifier = Constants::WORKER_EXIT): void
    {
        CoordinatorManager::until($identifier)->resume();
    }

    /**
     * Clear the coroutine that is blocked by the specified identifier.
     * Alias of `CoordinatorManager::clear($identifier)`.
     */
    public static function clear(string $identifier = Constants::WORKER_EXIT): void
    {
        CoordinatorManager::clear($identifier);
    }
}
