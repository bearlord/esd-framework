<?php

namespace ESD\Plugins\Redis;

use ESD\Core\Pool\Exception\ConnectionException;
use ESD\Plugins\Redis\Exception\InvalidRedisConnectionException;
use ESD\Server\Coroutine\Server;
use Redis;
use RedisCluster;
use RedisSentinel;

class RedisConnection
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $driverType;

    /**
     * @var \Redis|\RedisSentinel|\RedisCluster
     */
    protected $driver;


    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array $config
     * @return void
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getDriverType(): string
    {
        return $this->driverType;
    }

    public function setDriverType(string $driverType): void
    {
        $this->driverType = $driverType;
    }

    /**
     * @return \Redis|\RedisCluster|\RedisSentinel
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * @param \Redis|\RedisCluster|\RedisSentinel $driver
     */
    public function setDriver($driver): void
    {
        $this->driver = $driver;
    }

    /**
     * @return mixed|\Redis|\RedisCluster
     * @throws \ESD\Core\Pool\Exception\ConnectionException
     * @throws \ESD\Plugins\Redis\RedisException
     * @throws \RedisClusterException
     * @throws \RedisException
     */
    public function open()
    {
        $auth = $this->config['auth'];
        $database = $this->config['database'];
        $clusterConfig = $this->config['cluster']['enable'] ?? false;
        $sentinelConfig = $this->config['sentinel']['enable'] ?? false;

        switch (true) {
            case !empty($clusterConfig):
                $this->setDriverType("cluster");
                $redis = $this->createRedisCluster();
                break;

            case !empty($sentinelConfig):
                $this->setDriverType("sentinel");
                $redis = $this->createRedisSentinel();
                break;

            default:
                $this->setDriverType("redis");
                $redis = $this->createRedis($this->config);
        }

        $this->setDriver($redis);

        $options = $this->config['options'] ?? [];
        foreach ($options as $name => $value) {
            if (!empty($name)) {
                $optionNmae = $this->formatOptionName($name);
                if (!empty($optionNmae)) {
                    $redis->setOption($optionNmae, $value);
                }
            }
        }

        if ($redis instanceof Redis && isset($auth) && $auth !== '') {
            $redis->auth($auth);
        }

        $databaseSelect = $this->database ?? $database;
        if ($database > 0) {
            $redis->select($databaseSelect);
        }

        return $redis;
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function close(): void
    {
        Server::$instance->getLog()->debug('Closing Redis connection: ' . $this->config['name'] . "." . $this->getDriverType());
        $this->driver->close();
    }

    /**
     * @return \RedisCluster
     * @throws \ESD\Core\Pool\Exception\ConnectionException
     * @throws \RedisClusterException
     */
    protected function createRedisCluster(): RedisCluster
    {
        try {
            $parameters = [];
            $parameters[] = $this->config['cluster']['name'] ?? null;
            $parameters[] = $this->config['cluster']['seeds'] ?? [];
            $parameters[] = $this->config['timeout'] ?? 0.0;
            $parameters[] = $this->config['cluster']['readTimeout'] ?? 0.0;
            $parameters[] = $this->config['cluster']['persistent'] ?? false;
            if (isset($this->config['auth'])) {
                $parameters[] = $this->config['auth'];
            }
            if (!empty($this->config['cluster']['context'])) {
                $parameters[] = $this->config['cluster']['context'];
            }

            Server::$instance->getLog()->debug('Opening RedisCluster connection');

            $redis = new RedisCluster(...$parameters);
        } catch (Throwable $e) {
            throw new ConnectionException('Connection reconnect failed ' . $e->getMessage());
        }

        return $redis;
    }

    /**
     * @throws ConnectionException
     */
    protected function createRedisSentinel(): Redis
    {
        try {
            $nodes = $this->config['sentinel']['nodes'] ?? [];
            $timeout = $this->config['timeout'] ?? 0;
            $persistent = $this->config['sentinel']['persistent'] ?? null;
            $retryInterval = $this->config['retryInterval'] ?? 0;
            $readTimeout = $this->config['sentinel']['readTimeout'] ?? 0;
            $masterName = $this->config['sentinel']['masterName'] ?? '';
            $auth = $this->config['sentinel']['auth'] ?? null;

            shuffle($nodes);

            $host = null;
            $port = null;
            foreach ($nodes as $node) {
                try {
                    $resolved = parse_url($node);
                    if (!isset($resolved['host'], $resolved['port'])) {
                        Server::$instance->getLog()->error(sprintf('The redis sentinel node [%s] is invalid.', $node));
                        continue;
                    }
                    $options = [
                        'host' => $resolved['host'],
                        'port' => (int)$resolved['port'],
                        'connectTimeout' => $timeout,
                        'persistent' => $persistent,
                        'retryInterval' => $retryInterval,
                        'readTimeout' => $readTimeout,
                        ...($auth ? ['auth' => $auth] : []),
                    ];

                    Server::$instance->getLog()->debug('Opening RedisSentinel connection: ' . $resolved['name'] . '.' . $this->getDriverType());

                    $sentinel = (new RedisSentinelFactory())->create($options);
                    $masterInfo = $sentinel->getMasterAddrByName($masterName);
                    if (is_array($masterInfo) && count($masterInfo) >= 2) {
                        [$host, $port] = $masterInfo;
                        break;
                    }
                } catch (\Throwable $exception) {
                    Server::$instance->getLog()->error('Redis sentinel connection failed, caused by ' . $exception->getMessage());
                    continue;
                }
            }

            if ($host === null && $port === null) {
                throw new InvalidRedisConnectionException('Connect sentinel redis server failed.');
            }

            $redis = $this->createRedis([
                'host' => $host,
                'port' => $port,
                'timeout' => $timeout,
                'retryinterval' => $retryInterval,
                'readtimeout' => $readTimeout,
            ]);
        } catch (Throwable $e) {
            throw new ConnectionException('Connection reconnect failed ' . $e->getMessage());
        }

        return $redis;
    }

    /**
     * @param array $config
     * @return \Redis
     * @throws \ESD\Core\Pool\Exception\ConnectionException
     * @throws \RedisException
     */
    protected function createRedis(array $config): Redis
    {
        Server::$instance->getLog()->debug('Opening Redis connection: ' . $this->config['name'] . "." . $this->getDriverType());

        return $this->innerConnect($config);
    }

    /**
     * @param array $config
     * @return \Redis
     * @throws \ESD\Core\Pool\Exception\ConnectionException
     * @throws \RedisException
     */
    protected function innerConnect(array $config): Redis
    {
        $parameters = [
            $config['host'] ?? '',
            (int)($config['port'] ?? 6379),
            $config['timeout'] ?? 0.0,
            $config['reserved'] ?? null,
            $config['retryinterval'] ?? 0,
            $config['readtimeout'] ?? 0.0,
        ];

        if (!empty($config['context'])) {
            $parameters[] = $config['context'];
        }

        $redis = new Redis();
        if (!$redis->connect(...$parameters)) {
            throw new ConnectionException('Connection reconnect failed.');
        }

        return $redis;
    }

    /**
     * @param string $name
     * @param array|null $params
     * @return mixed
     * @throws \Exception
     */
    public function __call(string $name, ?array $params = [])
    {
        try {
            return call_user_func_array([$this->driver, $name], $params);
        } catch (\Throwable $exception) {
            Server::$instance->getLog()->error('Redis Execute Command failed: ' . $exception->getMessage());
            throw new \Exception($exception->getMessage());
        }
    }

}
