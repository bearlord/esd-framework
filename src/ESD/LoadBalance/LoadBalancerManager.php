<?php
/**
 * Copied from hyperf, and modifications are not listed anymore.
 * @contact  group@hyperf.io
 * @licence  MIT License
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace ESD\LoadBalance;

use ESD\LoadBalance\Algorithm\Random;
use ESD\LoadBalance\Algorithm\RoundRobin;
use ESD\LoadBalance\Algorithm\WeightedRandom;
use ESD\LoadBalance\Algorithm\WeightedRoundRobin;
use InvalidArgumentException;

/**
 * Class LoadBalancerManager
 * @package ESD\LoadBalance
 */
class LoadBalancerManager
{
    /**
     * @var array
     */
    private $algorithms = [
        'random' => Random::class,
        'round-robin' => RoundRobin::class,
        'weighted-random' => WeightedRandom::class,
        'weighted-round-robin' => WeightedRoundRobin::class,
    ];

    private $instances = [];

    /**
     * Retrieve a class name of load balancer.
     */
    public function get(string $name): string
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException(sprintf('The %s algorithm does not exists.', $name));
        }
        return $this->algorithms[$name];
    }

    /**
     * Retrieve a class name of load balancer and create a object instance,
     * If $container object exists, then the class will create via container.
     *
     * @param string $key key of the load balancer instance
     * @param string $algorithm The name of the load balance algorithm
     */
    public function getInstance(string $key, string $algorithm): LoadBalancerInterface
    {
        if (isset($this->instances[$key])) {
            return $this->instances[$key];
        }
        $class = $this->get($algorithm);
        $instance = new $class();

        $this->instances[$key] = $instance;
        return $instance;
    }

    /**
     * Determire if the algorithm is exists.
     */
    public function has(string $name): bool
    {
        return isset($this->algorithms[$name]);
    }

    /**
     * Override the algorithms.
     */
    public function set(array $algorithms): self
    {
        foreach ($algorithms as $algorithm) {
            if (!class_exists($algorithm)) {
                throw new InvalidArgumentException(sprintf('The class of %s algorithm does not exists.', $algorithm));
            }
        }
        $this->algorithms = $algorithms;
        return $this;
    }

    /**
     * Register a algorithm to the manager.
     */
    public function register(string $key, string $algorithm): self
    {
        if (!class_exists($algorithm)) {
            throw new InvalidArgumentException(sprintf('The class of %s algorithm does not exists.', $algorithm));
        }
        $this->algorithms[$key] = $algorithm;
        return $this;
    }


}
