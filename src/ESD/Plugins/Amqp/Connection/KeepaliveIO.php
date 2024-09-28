<?php
/**
 * Copied from hyperf, and modifications are not listed anymore.
 * @contact  group@hyperf.io
 * @licence  MIT License
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace ESD\Plugins\Amqp\Connection;

use ESD\Core\DI\DI;
use ESD\Core\Exception;
use ESD\Server\Coroutine\Server;
use InvalidArgumentException;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Wire\IO\AbstractIO;
use Swoole\Coroutine\Client;

class KeepaliveIO extends AbstractIO
{
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
    protected $connectionTimeout;

    /**
     * @var float
     */
    protected $readWriteTimeout;

    /**
     * @var resource
     */
    protected $context;

    /**
     * @var bool
     */
    protected $keepalive;

    /**
     * @var int
     */
    protected $heartbeat;

    /**
     * @var int
     */
    private $initialHeartbeat;

    /**
     * @var Socket
     */
    private $sock;

    /**
     * @var string
     */
    private $buffer = '';

    /** @var AbstractConnection */
    protected $connContext;

    /**
     * SwooleIO constructor.
     *
     * @inheritDoc
     * @param null|mixed $context
     * @throws \InvalidArgumentException when readWriteTimeout argument does not 2x the heartbeat
     */
    public function __construct(
        string $host,
        int    $port,
        float  $connectionTimeout,
        float  $readWriteTimeout,
               $context = null,
        bool   $keepalive = false,
        int    $heartbeat = 0
    )
    {
        if ($heartbeat !== 0 && ($readWriteTimeout < ($heartbeat * 2))) {
            throw new InvalidArgumentException('Argument readWriteTimeout must be at least 2x the heartbeat.');
        }
        $this->host = $host;
        $this->port = $port;
        $this->connectionTimeout = $connectionTimeout;
        $this->readWriteTimeout = $readWriteTimeout;
        $this->context = $context;
        $this->keepalive = $keepalive;
        $this->heartbeat = $heartbeat;
        $this->initialHeartbeat = $heartbeat + 3;
    }

    /**
     * @inheritDoc
     * @return AbstractConnection
     */
    public function getConnContext(): AbstractConnection
    {
        return $this->connContext;
    }

    /**
     * @inheritDoc
     * @param AbstractConnection $connContext
     */
    public function setConnContext(AbstractConnection $connContext): void
    {
        $this->connContext = $connContext;
    }


    /**
     * Sets up the stream connection.
     * @inheritDoc
     */
    public function connect()
    {
        $this->sock = new Socket($this->host, $this->port, $this->connectionTimeout, $this->heartbeat);
    }

    /**
     * Reconnects the socket.
     * @inheritDoc
     */
    public function reconnect()
    {
        $this->close();
        $this->connect();
    }

    /**
     * @inheritDoc
     * @param int $len
     * @return string
     * @throws AMQPRuntimeException
     */
    public function read($len)
    {
        return $this->sock->call(function (Client $client) use ($len) {
            do {
                if ($len <= strlen($this->buffer)) {
                    $data = substr($this->buffer, 0, $len);
                    $this->buffer = substr($this->buffer, $len);

                    return $data;
                }

                if (!$client->connected) {
                    throw new AMQPRuntimeException('Broken pipe or closed connection');
                }

                $read_buffer = $client->recv($this->readWriteTimeout ? $this->readWriteTimeout : -1);
                if ($read_buffer === false) {
                    throw new AMQPRuntimeException('Error receiving data, errno=' . $client->errCode);
                }

                if ($read_buffer === '') {
                    throw new AMQPRuntimeException('Connection is closed.');
                }

                $this->buffer .= $read_buffer;
            } while (true);
        });
    }

    /**
     * @inheritDoc
     * @param string $data
     * @throws AMQPRuntimeException
     */
    public function write($data)
    {
        $this->sock->call(function ($client) use ($data) {
            $buffer = $client->send($data);

            if ($buffer === false) {
                throw new AMQPRuntimeException('Error sending data');
            }
        });
    }

    /**
     * @inheritDoc
     * No effect in KeeyaliveIO.
     */
    public function check_heartbeat()
    {
    }

    /**
     * @inheritDoc
     * @return void
     */
    public function close()
    {
        try {
            if (isset($this->sock) && $this->sock instanceof Socket) {
                $this->sock->close();
            }
        } catch (Exception $exception) {
            throw $exception;
        }

    }

    /**
     * @inheritDoc
     * @return Socket|resource
     */
    public function getSocket()
    {
        return $this->sock;
    }

    /**
     * @inheritDoc
     * @param int $sec
     * @param int $usec
     * @return int
     */
    public function select($sec, $usec)
    {
        return 1;
    }

    /**
     * @inheritDoc
     * @return $this
     */
    public function disableHeartbeat()
    {
        $this->heartbeat = 0;

        return $this;
    }

    /**
     * @inheritDoc
     * @return $this
     */
    public function reenableHeartbeat()
    {
        $this->heartbeat = $this->initialHeartbeat;

        return $this;
    }

    /**
     * Sends a heartbeat message.
     * @inheritDoc
     */
    protected function write_heartbeat()
    {
        $this->sock->heartbeat();
    }

    /**
     * @inheritDoc
     * @param $sec
     * @param $usec
     * @return int
     */
    protected function do_select($sec, $usec)
    {
        return 1;
    }
}
