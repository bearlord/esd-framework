<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Server\Beans;

/**
 * Class ClientInfo
 * @package ESD\Core\Server\Beans
 */
class ClientInfo
{
    /**
     * Reactor thread
     * @var int
     */
    private $reactorId;

    /**
     * Server fd, not the fd of the client connection
     * @var int
     */
    private $serverFd;

    /**
     * Server port
     * @var int
     */
    private $serverPort;

    /**
     * 客户端连接的端口
     * @var int
     */
    private $remotePort;

    /**
     * IP address of the client connection
     * @var int
     */
    private $remoteIp;

    /**
     * Time for the client to connect to the server, in seconds, set by the master process
     * @var int
     */
    private $connectTime;

    /**
     * Last time data was received, in seconds, set by the master process
     * @var int
     */
    private $lastTime;

    /**
     * Connection close error code. If the connection is closed abnormally,
     * the value of close_errno is non-zero. You can refer to the Linux error message list.
     * @var int
     */
    private $closeErrno;

    /**
     * [Optional] WebSocket connection status,
     * this information will be added when the server is Swoole\WebSocket\Server
     * @var int
     */
    private $websocketStatus;

    /**
     * [Optional] Use SSL tunnel encryption and add this information when the client sets a certificate
     * @var null
     */
    private $sslClientCert;

    /**
     * [Optional] This information will be added when bind user ID with bind
     * @var int
     */
    private $uid;

    /**
     * ClientInfo constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->reactorId = $data['reactor_id']??null;
        $this->serverFd = $data['server_fd']??null;
        $this->serverPort = $data['server_port']??null;
        $this->remotePort = $data['remote_port']??null;
        $this->remoteIp = $data['remote_ip']??null;
        $this->connectTime = $data['connect_time']??null;
        $this->lastTime = $data['last_time']??null;
        $this->closeErrno = $data['close_errno']??null;
        $this->websocketStatus = $data['websocket_status']??null;
        $this->sslClientCert = $data['ssl_client_cert']??null;
        $this->uid = $data['uid']??null;
    }

    /**
     * @return int
     */
    public function getReactorId()
    {
        return $this->reactorId;
    }

    /**
     * @return int
     */
    public function getServerFd()
    {
        return $this->serverFd;
    }

    /**
     * @return int
     */
    public function getServerPort()
    {
        return $this->serverPort;
    }

    /**
     * @return int
     */
    public function getRemotePort()
    {
        return $this->remotePort;
    }

    /**
     * @return int
     */
    public function getRemoteIp()
    {
        return $this->remoteIp;
    }

    /**
     * @return int
     */
    public function getConnectTime()
    {
        return $this->connectTime;
    }

    /**
     * @return int
     */
    public function getLastTime()
    {
        return $this->lastTime;
    }

    /**
     * @return int
     */
    public function getCloseErrno()
    {
        return $this->closeErrno;
    }

    /**
     * @return int
     */
    public function getWebsocketStatus()
    {
        return $this->websocketStatus;
    }

    /**
     * @return int
     */
    public function getSslClientCert()
    {
        return $this->sslClientCert;
    }

    /**
     * @return int
     */
    public function getUid()
    {
        return $this->uid;
    }
}