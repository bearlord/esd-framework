<?php

namespace ESD\Plugins\Amqp\Connection;

use ESD\Core\Server\Server;
use PhpAmqpLib\Connection\AbstractConnection;
use function DI\string;

class AMQPSwooleConnection extends AbstractConnection
{
    /**
     * @param string $host
     * @param int $port
     * @param string $user
     * @param string $password
     * @param string $vhost
     * @param bool $insist
     * @param string $loginMethod
     * @param $loginResponse
     * @param string $locale
     * @param float $connectionTimeout
     * @param float $readWriteTimeout
     * @param $context
     * @param bool $keepalive
     * @param int $heartbeat
     * @throws \Exception
     */
    public function __construct(
        string $host,
        int    $port,
        string $user,
        string $password,
        string $vhost = '/',
        bool   $insist = false,
        string $loginMethod = 'AMQPLAIN',
               $loginResponse = null,
        string $locale = 'en_US',
        float  $connectionTimeout = 3.0,
        float  $readWriteTimeout = 3.0,
               $context = null,
        bool   $keepalive = false,
        int    $heartbeat = 0
    )
    {
        if ($keepalive) {
            $io = new KeepaliveIO($host, $port, $connectionTimeout, $readWriteTimeout, $context, $keepalive, $heartbeat);
            $io->setConnContext($this);
        } else {
            $io = new SwooleIO($host, $port, $connectionTimeout, $readWriteTimeout, $context, $keepalive, $heartbeat);
        }

        parent::__construct(
            $user,
            $password,
            $vhost,
            $insist,
            $loginMethod,
            $loginResponse,
            $locale,
            $io,
            $heartbeat,
            (int)$connectionTimeout
        );
    }

    /**
     * @inheritDoc
     * @return \PhpAmqpLib\Wire\IO\AbstractIO
     */
    public function getIO()
    {
        return $this->io;
    }
}
