<?php
/**
 * Copied from hyperf, and modifications are not listed anymore.
 * @contact  group@hyperf.io
 * @licence  MIT License
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace ESD\Plugins\Amqp\Connection;

use ESD\Core\Exception;
use ESD\Core\Server\Server;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Utils\ApplicationContext;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Wire\AMQPWriter;
use Psr\Log\LoggerInterface;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\Client;
use Swoole\Timer;

class Socket
{
    /**
     * @var Channel
     */
    protected $channel;

    /**
     * @var null|int
     */
    protected $timerId;

    /**
     * @var bool
     */
    protected $connected = false;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var float
     */
    protected $timeout;

    /**
     * @var int
     */
    protected $heartbeat;

    /**
     * @var float
     */
    protected $waitTimeout = 10.0;

    /**
     * @var float
     */
    protected $lastHeartbeatTime = 0.0;

    protected $client;

    /**
     * @param string $host
     * @param int $port
     * @param float $timeout
     * @param int $heartbeat
     */
    public function __construct(string $host, int $port, float $timeout, int $heartbeat)
    {
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
        $this->heartbeat = $heartbeat;

        $this->connect();
    }

    /**
     * @inheritDoc
     * @return float
     */
    public function getLastHeartbeatTime(): float
    {
        return $this->lastHeartbeatTime;
    }

    /**
     * @inheritDoc
     * @param float $lastHeartbeatTime
     */
    public function setLastHeartbeatTime(float $lastHeartbeatTime): void
    {
        $this->lastHeartbeatTime = $lastHeartbeatTime;
    }
    

    /**
     * @inheritDoc
     * @param \Closure $closure
     * @return mixed
     */
    public function call(\Closure $closure)
    {
        if (!$this->isConnected()) {
            $this->connect();
        }

        $client = $this->client;
        if ($client === false) {
            throw new AMQPRuntimeException('Socket of keepaliveIO is exhausted. Cannot establish new socket before wait_timeout.');
        }

        try {
            $result = $closure($client);
        } catch (Exception $exception) {
            Server::$instance->getLog()->error((string) $exception);
        }

        return $result;
    }

    /**
     * @return void
     */
    public function connect()
    {
        $sock = new Client(SWOOLE_SOCK_TCP);
        if (!$sock->connect($this->host, $this->port, $this->timeout)) {
            throw new AMQPRuntimeException(
                sprintf(
                    'Error Connecting to server(%s): %s ',
                    $sock->errCode,
                    swoole_strerror($sock->errCode)
                ),
                $sock->errCode
            );
        }

        $this->client = $sock;
        $this->connected = true;

        $this->addHeartbeat();
    }

    /**
     * @inheritDoc
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->connected;
    }

    /**
     * @inheritDoc
     * @return void
     */
    public function close()
    {
        $this->connected = false;
        $this->clear();
    }

    /**
     * Sends a heartbeat message.
     * @inheritDoc
     */
    public function heartbeat()
    {
        $pkt = new AMQPWriter();
        $pkt->write_octet(8);
        $pkt->write_short(0);
        $pkt->write_long(0);
        $pkt->write_octet(0xCE);
        $data = $pkt->getvalue();

        $this->call(function ($client) use ($data) {
            $buffer = $client->send($data);
            if ($buffer === false) {
                throw new AMQPRuntimeException('Error sending data');
            }
        });

        $this->lastHeartbeatTime = microtime(true);
    }

    /**
     * @inheritDoc
     * @return void
     */
    protected function addHeartbeat()
    {
        $this->clear();

        $this->lastHeartbeatTime = microtime(true);
        $this->timerId = Timer::tick($this->heartbeat * 1000, function () {
            try {
                if ($this->isConnected()) {
                    $this->heartbeat();
                }
            } catch (\Throwable $throwable) {
                $this->close();
                $message = sprintf('KeepaliveIO heartbeat failed, %s', (string)$throwable);
                DIGet(LoggerInterface::class)->error($message);
            }
        });
    }

    /**
     * @inheritDoc
     * @return void
     */
    protected function clear()
    {
        if ($this->timerId) {
            Timer::clear($this->timerId);
            $this->timerId = null;
        }
    }

    /**
     * @inheritDoc
     */
    public function __destruct()
    {
        $this->clear();
    }
}
