<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Client;

use ESD\Core\Server\Config\PortConfig;
use Swoole;

/**
 * Class Client
 * @package ESD\Core\Client
 */
class Client
{
    /**
     * @var Swoole\Coroutine\Client
     */
    protected $swooleClient;

    /**
     * Client constructor.
     *
     * @param int $sock_type
     */
    public function __construct(int $sock_type = PortConfig::SWOOLE_SOCK_TCP)
    {
        $this->swooleClient = new Swoole\Coroutine\Client($sock_type);
    }

    /**
     * The connect operation will have a coroutine switching overhead, yield is initiated when to connect is initiated, and resume is completed when to connect is completed
     *
     * @param string $host The address of the remote server. 2.0.12 or later can be passed directly to the domain name. The bottom layer will automatically perform coroutine switching to resolve the domain name into an IP address.
     * @param int $port The remote server port
     * @param float $timeout The timeout time of the network IO, including connect/send/recv, the unit is seconds, and supports floating point numbers. The default is 0.5s/100ms. When the timeout occurs, the connection will be automatically closed.
     * @param int $sock_flag
     * @return bool
     */
    public function connect(string $host, int $port, float $timeout = 0.5, int $sock_flag = 0): bool
    {
        return $this->swooleClient->connect($host, $port, $timeout, $sock_flag);
    }

    /**
     * The send successfully returns the number of bytes written to the socket buffer area, and the bottom layer will send all data as much as possible.
     * If the number of bytes returned is different from the length of the incoming $data, the socket may have been closed by the peer, and the next time you call send or recv, the corresponding error code will be returned.
     *
     * @param string $data
     * @return false|int
     */
    public function send(string $data)
    {
        return $this->swooleClient->send($data);
    }

    /**
     * Receive data form server
     * The bottom layer will be automatically yielded, and it will automatically switch to the current coroutine after receiving data.
     * Set communication protocol, recv will return complete data, the length is limited by package_max_length
     * No communication protocol is set, recv returns up to 64K data
     * The communication protocol is not set to return the original data. You need to implement the network protocol processing in PHP code yourself.
     * recv returns an empty string indicating that the server actively closed the connection, which requires close
     * recv fails, returns false, detects $client->errCode to get the cause of the error
     *
     * @param float $timeout If $timeout is passed in, the specified timeout parameter is used preferentially.
     * If $timeout is not passed in, but a timeout is specified during connect, and the connect timeout is automatically used as the recv timeout
     * No $timeout is passed in, no connect timeout is set, setting it to -1 means never timeout
     * Timeout error code is ETIMEDOUT
     * @return string
     */
    public function recv(float $timeout = -1): string
    {
        return $this->swooleClient->recv($timeout);
    }

    /**
     * Close the connection.
     * close does not block and will return immediately.
     *
     * @return bool if the execution is successful, false if it fails
     */
    public function close(): bool
    {
        return $this->swooleClient->close();
    }

    /**
     * Peep data.
     * The peek method directly operates the socket, so it will not cause coroutine scheduling.
     * The peek method is only used to peek at the data in the kernel socket buffer area, without offset. After using peek, you can still read this part of data by calling recv.
     * The peek method is non-blocking and it returns immediately. When there is data in the socket cache, the data content is returned. Returns false when the cache area is empty, and sets $client->errCode
     * Connection has been closed peek returns empty string
     *
     * @param int $length
     * @return string
     */
    public function peek(int $length = 65535): string
    {
        return $this->swooleClient->peek($length);
    }

    /**
     * Set client config
     *
     * @param ClientConfig $clientConfig
     * @throws \ReflectionException
     */
    public function set(ClientConfig $clientConfig)
    {
        $this->swooleClient->set($clientConfig->toConfigArray());
    }

    /**
     * Get server-side certificate information.
     * Successful execution returns an X509 certificate string information, Failed to return false
     * This method must be called after the SSL handshake is complete
     * You can use the openssl_x509_parse function provided by the openssl extension to parse the certificate information
     *
     * @return mixed
     */
    public function getPeerCert()
    {
        return $this->swooleClient->getPeerCert();
    }

    /**
     * 返回swoole_client的连接状态
     * @return mixed
     */
    public function isConnected()
    {
        return $this->swooleClient->isConnected();
    }

    /**
     * Obtain the local host: port of client socket.
     * return an array successfully，as：array('host' => '127.0.0.1', 'port' => 53652)
     *
     * @return array|false
     */
    public function getSockName()
    {
        return $this->swooleClient->getSockName();
    }

    /**
     * Get the IP address and port of the peer socket. Only Swoole_client objects of type SWOOLE_SOCK_UDP/SWOOLE_SOCK_UDP6 are supported.
     *
     * @return mixed
     */
    public function getPeerName()
    {
        return $this->swooleClient->getPeerName();
    }

    /**
     * Send UDP packets to any IP: PORT host. Only Swoole_client objects of type SWOOLE_SOCK_UDP/SWOOLE_SOCK_UDP6 are supported.
     *
     * @param string $ip
     * @param int $port
     * @param string $data
     */
    public function sendTo(string $ip, int $port, string $data)
    {
        $this->swooleClient->sendto($ip, $port, $data);
    }

    /**
     * Send a file to the server. This function is implemented based on the sendfile operating system call.
     *
     * @param string $filename filename to be sent
     * @param int $offset The offset of the uploaded file. You can specify that data is transferred from the middle of the file. This feature can be used to support breakpoint resumes.
     * @param int $length The size of the sent data. The default is the size of the entire file.
     * @return bool If the incoming file does not exist, it will return false and return true if the execution is successful
     */
    public function sendFile(string $filename, int $offset = 0, int $length = 0): bool
    {
        return $this->swooleClient->sendfile($filename, $offset, $length);
    }

    /**
     * Dynamically enable SSL tunnel encryption.
     * The client uses clear text communication when establishing a connection, and hopes to change to SSL tunnel encrypted communication halfway through the use of the enableSSL method.
     * To enable SSL tunnel encryption dynamically using enableSSL, two conditions must be met:
     * Client must be non-SSL when creating
     * The client has established a connection with the server
     */
    public function enableSSL()
    {
        $this->swooleClient->enableSSL();
    }

    /**
     * Get error code
     *
     * @return int
     */
    public function getErrCode(): int
    {
        return $this->swooleClient->errCode();
    }

    /**
     * Get error string
     *
     * @return string
     */
    public function getErrStr(): string
    {
        return socket_strerror($this->swooleClient->errCode);
    }

    /**
     * Get sock
     *
     * @return int
     */
    public function getSock(): int
    {
        return $this->swooleClient->sock;
    }

    /**
     * Indicates whether this connection is newly created or reuses an existing one. Used in conjunction with SWOOLE_KEEP.
     *
     * @return mixed
     */
    public function getReuse(): bool
    {
        return $this->swooleClient->reuse;
    }
}
