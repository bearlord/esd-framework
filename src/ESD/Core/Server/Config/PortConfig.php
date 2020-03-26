<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Server\Config;

use ESD\Core\Plugins\Config\BaseConfig;
use ESD\Core\Plugins\Config\ConfigException;

/**
 * Class PortConfig
 * @package ESD\Core\Server\Config
 */
class PortConfig extends BaseConfig
{
    const SWOOLE_SOCK_TCP = SWOOLE_SOCK_TCP;
    const SWOOLE_SOCK_TCP6 = SWOOLE_SOCK_TCP6;
    const SWOOLE_SOCK_UDP = SWOOLE_SOCK_UDP;
    const SWOOLE_SOCK_UDP6 = SWOOLE_SOCK_UDP6;
    const SWOOLE_SOCK_UNIX_DGRAM = SWOOLE_SOCK_UNIX_DGRAM;
    const SWOOLE_SOCK_UNIX_STREAM = SWOOLE_SOCK_UNIX_STREAM;
    const SWOOLE_SSL = SWOOLE_SSL;

    const SWOOLE_SSLv3_METHOD = SWOOLE_SSLv3_METHOD;
    const SWOOLE_SSLv3_SERVER_METHOD = SWOOLE_SSLv3_SERVER_METHOD;
    const SWOOLE_SSLv3_CLIENT_METHOD = SWOOLE_SSLv3_CLIENT_METHOD;
    const SWOOLE_SSLv23_METHOD = SWOOLE_SSLv23_METHOD;
    const SWOOLE_SSLv23_SERVER_METHOD = SWOOLE_SSLv23_SERVER_METHOD;
    const SWOOLE_SSLv23_CLIENT_METHOD = SWOOLE_SSLv23_CLIENT_METHOD;
    const SWOOLE_TLSv1_METHOD = SWOOLE_TLSv1_METHOD;
    const SWOOLE_TLSv1_SERVER_METHOD = SWOOLE_TLSv1_SERVER_METHOD;
    const SWOOLE_TLSv1_CLIENT_METHOD = SWOOLE_TLSv1_CLIENT_METHOD;
    const SWOOLE_TLSv1_1_METHOD = SWOOLE_TLSv1_1_METHOD;
    const SWOOLE_TLSv1_1_SERVER_METHOD = SWOOLE_TLSv1_1_SERVER_METHOD;
    const SWOOLE_TLSv1_1_CLIENT_METHOD = SWOOLE_TLSv1_1_CLIENT_METHOD;
    const SWOOLE_TLSv1_2_METHOD = SWOOLE_TLSv1_2_METHOD;
    const SWOOLE_TLSv1_2_SERVER_METHOD = SWOOLE_TLSv1_2_SERVER_METHOD;
    const SWOOLE_TLSv1_2_CLIENT_METHOD = SWOOLE_TLSv1_2_CLIENT_METHOD;
    const SWOOLE_DTLSv1_METHOD = SWOOLE_DTLSv1_METHOD;
    const SWOOLE_DTLSv1_SERVER_METHOD = SWOOLE_DTLSv1_SERVER_METHOD;
    const SWOOLE_DTLSv1_CLIENT_METHOD = SWOOLE_DTLSv1_CLIENT_METHOD;

    const WEBSOCKET_OPCODE_TEXT = WEBSOCKET_OPCODE_TEXT;
    const WEBSOCKET_OPCODE_BINARY = WEBSOCKET_OPCODE_BINARY;
    const WEBSOCKET_OPCODE_PING = WEBSOCKET_OPCODE_PING;
    const WEBSOCKET_STATUS_CONNECTION = WEBSOCKET_STATUS_CONNECTION;
    const WEBSOCKET_STATUS_HANDSHAKE = WEBSOCKET_STATUS_HANDSHAKE;
    const WEBSOCKET_STATUS_FRAME = WEBSOCKET_STATUS_FRAME;

    const key = "esd.port";

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $portClass;

    /**
     * default Listening ip. 127.0.0.1 for ipv4, and ::1 for ipv6
     * @var string
     */
    protected $host;

    /**
     * default Listening port
     * @var int
     */
    protected $port;

    /**
     * Socket type
     * @var int
     */
    protected $sockType;

    /**
     * Enable ssl
     * @var bool
     */
    protected $enableSsl;

    /**
     * Listening queue length, effect the accept connections at the same time
     * @var int
     */
    protected $backlog;

    /**
     * Open TCP no delay property, and close Nagle algorithm
     * @var bool
     */
    protected $openTcpNodelay;

    /**
     * Open TCP fast handshake property
     * @var bool
     */
    protected $tcpFastopen;

    /**
     * Tcp defer accept time
     * @var int
     */
    protected $tcpDeferAccept;

    /**
     * Open EOF detection. This option will detect the data sent by the client connection.
     * It will be delivered to the Worker process only when the end of the data packet is the specified string.
     * Otherwise, data packets will be concatenated until the buffer area is exceeded or timeout will not be aborted.
     * When an error occurs, the underlying layer will consider it a malicious connection,
     * discard the data and force the connection to be closed.
     * @var bool
     */
    protected $openEofCheck;

    /**
     * Enable EOF automatic subcontracting.
     * When open_eof_check is set, the underlying detection data is buffered with a specific string at the end,
     * but only the end of the received data is intercepted for comparison by default.
     * At this time, multiple pieces of data may be merged into one package.
     * @var bool
     */
    protected $openEofSplit;

    /**
     * Used with open_eof_check or open_eof_split to set EOF strings.
     * @var string
     */
    protected $packageEof;

    /**
     * turn on the packet length detection feature. Packet length detection provides parsing of the fixed header and body format.
     * After it is enabled, it can be guaranteed that the Worker process onReceive will receive a complete packet each time.
     * @var bool
     */
    protected $openLengthCheck;

    /**
     * Package length type, consistent with pack function. Swoole support 10 types：
     *
     * c: signed, 1 bytes
     * C: unsigned, 1 bytes
     * s: signed, Host byte order, 2 bytes
     * S: unsigned, Host byte order, 2 bytes
     * n: unsigned, network byte order, 2 bytes
     * N: unsigned, network byte order, 4 bytes
     * l: signed, Host byte order, 4 bytes
     * L: unsigned, Host byte order, 4 bytes
     * v: unsigned, little-endian、2 bytes
     * V: unsigned, little-endian、4 bytes
     * @var string
     */
    protected $packageLengthType;

    /**
     * Set the maximum packet size in bytes
     * @var int
     */
    protected $packageMaxLength;

    /**
     * Calculate the length from the few bytes, there are generally two cases:
     * The value of length contains the entire package (header + body), package_body_offset is 0
     * The length of the header is N bytes. The value of length does not include the header, only the body of the package. The package_body_offset is set to N.
     * @var int
     */
    protected $packageBodyOffset;

    /**
     * Package length offset
     * @var int
     */
    protected $packageLengthOffset;

    /**
     * SSL cert file
     * @var string
     */
    protected $sslCertFile;

    /**
     * SSL ciphers. After enabling SSL, set ssl_ciphers to change the default encryption algorithm of openssl. Swoole support EECDH+AESGCM:EDH+AESGCM:AES256+EECDH:AES256+EDH
     * @var string
     */
    protected $sslCiphers;

    /**
     * SSL Tunnel encryption algorithm, default is SWOOLE_SSLv23_METHOD
     * @var string
     */
    protected $sslMethod;

    /**
     * Open http protocol
     * @var bool
     */
    protected $openHttpProtocol;

    /**
     * Open websocket protocol
     * @var bool
     */
    protected $openWebsocketProtocol;

    /**
     * Open mqtt protocol.
     * It will parse the mqtt packet header, and the worker process onReceive will return a complete mqtt packet each time.
     * @var bool
     */
    protected $openMqttProtocol;

    /**
     * Open websocket close frame.Enable closing frames (frames with opcode 0x08) in the websocket protocol to be received in the onMessage callback
     *
     * @var bool
     */
    protected $openWebsocketCloseFrame;

    /**
     * SSL verify peer. default closed. if opened, You must also set the ssl_client_cert_file option
     * @var bool
     */
    protected $sslVerifyPeer;

    /**
     * SSL client cert file
     * @var string
     */
    protected $sslClientCertFile;

    /**
     * Enable reuse port.This parameter is used to optimize the Accept performance of the TCP connection.
     * After enabling port reuse, multiple processes can perform Accept operations simultaneously.
     * @var bool
     */
    protected $enableReusePort;

    /**
     * Enable delay receive. After setting this option to true,
     * the accept client will not automatically join the EventLoop after connecting, only the onConnect callback will be triggered.
     * The worker process can call $serv->confirm ($fd) to confirm the connection,
     * at this time fd will be added to the EventLoop to start data transmission and reception, or you can call $serv->close($fd) to close the connection.
     * @var bool
     */
    protected $enableDelayReceive;

    /**
     * Customer handshake
     * @var bool
     */
    protected $customHandShake = false;

    /**
     * Websocket opcode text
     *
     * @var int
     */
    protected $wsOpcode = self::WEBSOCKET_OPCODE_TEXT;

    public function __construct()
    {
        parent::__construct(self::key, true, "name");
    }

    /**
     * @return int
     */
    public function getBacklog()
    {
        return $this->backlog ?? 128;
    }

    /**
     * @param int $backlog
     */
    public function setBacklog(int $backlog)
    {
        $this->backlog = $backlog;
    }

    /**
     * @return bool
     */
    public function isOpenTcpNodelay()
    {
        return $this->openTcpNodelay ?? true;
    }

    /**
     * @param bool $openTcpNodelay
     */
    public function setOpenTcpNodelay(bool $openTcpNodelay)
    {
        $this->openTcpNodelay = $openTcpNodelay;
    }

    /**
     * @return bool
     */
    public function isTcpFastopen()
    {
        return $this->tcpFastopen ?? true;
    }

    /**
     * @param bool $tcpFastopen
     */
    public function setTcpFastopen(bool $tcpFastopen)
    {
        $this->tcpFastopen = $tcpFastopen;
    }

    /**
     * @return int
     */
    public function getTcpDeferAccept()
    {
        return $this->tcpDeferAccept ?? null;
    }

    /**
     * @param int $tcpDeferAccept
     */
    public function setTcpDeferAccept(int $tcpDeferAccept)
    {
        $this->tcpDeferAccept = $tcpDeferAccept;
    }

    /**
     * @return bool
     */
    public function isOpenEofCheck()
    {
        return $this->openEofCheck ?? false;
    }

    /**
     * @param bool $openEofCheck
     */
    public function setOpenEofCheck(bool $openEofCheck)
    {
        $this->openEofCheck = $openEofCheck;
    }

    /**
     * @return bool
     */
    public function isOpenEofSplit()
    {
        return $this->openEofSplit ?? false;
    }

    /**
     * @param bool $openEofSplit
     */
    public function setOpenEofSplit(bool $openEofSplit)
    {
        $this->openEofSplit = $openEofSplit;
    }

    /**
     * @return string
     */
    public function getPackageEof()
    {
        return $this->packageEof;
    }

    /**
     * @param string $packageEof
     */
    public function setPackageEof(string $packageEof)
    {
        $this->packageEof = $packageEof;
    }

    /**
     * @return bool
     */
    public function isOpenLengthCheck()
    {
        return $this->openLengthCheck ?? false;
    }

    /**
     * @param bool $openLengthCheck
     */
    public function setOpenLengthCheck(bool $openLengthCheck)
    {
        $this->openLengthCheck = $openLengthCheck;
    }

    /**
     * @return string
     */
    public function getPackageLengthType()
    {
        return $this->packageLengthType;
    }

    /**
     * @param string $packageLengthType
     */
    public function setPackageLengthType(string $packageLengthType)
    {
        $this->packageLengthType = $packageLengthType;
    }

    /**
     * @return int
     */
    public function getPackageMaxLength()
    {
        return $this->packageMaxLength;
    }

    /**
     * @param int $packageMaxLength
     */
    public function setPackageMaxLength(int $packageMaxLength)
    {
        $this->packageMaxLength = $packageMaxLength;
    }

    /**
     * @return int
     */
    public function getPackageBodyOffset()
    {
        return $this->packageBodyOffset;
    }

    /**
     * @param int $packageBodyOffset
     */
    public function setPackageBodyOffset(int $packageBodyOffset)
    {
        $this->packageBodyOffset = $packageBodyOffset;
    }

    /**
     * @return int
     */
    public function getPackageLengthOffset()
    {
        return $this->packageLengthOffset;
    }

    /**
     * @param int $packageLengthOffset
     */
    public function setPackageLengthOffset(int $packageLengthOffset)
    {
        $this->packageLengthOffset = $packageLengthOffset;
    }

    /**
     * @return string
     */
    public function getSslCertFile()
    {
        return $this->sslCertFile;
    }

    /**
     * @param string $sslCertFile
     */
    public function setSslCertFile(string $sslCertFile)
    {
        $this->sslCertFile = $sslCertFile;
    }

    /**
     * @return string
     */
    public function getSslCiphers()
    {
        return $this->sslCiphers;
    }

    /**
     * @param string $sslCiphers
     */
    public function setSslCiphers(string $sslCiphers)
    {
        $this->sslCiphers = $sslCiphers;
    }

    /**
     * @return bool
     */
    public function isOpenHttpProtocol()
    {
        return $this->openHttpProtocol ?? false;
    }

    /**
     * @param bool $openHttpProtocol
     */
    public function setOpenHttpProtocol(bool $openHttpProtocol)
    {
        $this->openHttpProtocol = $openHttpProtocol;
    }

    /**
     * @return bool
     */
    public function isOpenWebsocketProtocol()
    {
        return $this->openWebsocketProtocol ?? false;
    }

    /**
     * @param bool $openWebsocketProtocol
     */
    public function setOpenWebsocketProtocol(bool $openWebsocketProtocol)
    {
        $this->openWebsocketProtocol = $openWebsocketProtocol;
    }

    /**
     * @return bool
     */
    public function isOpenMqttProtocol()
    {
        return $this->openMqttProtocol ?? false;
    }

    /**
     * @param bool $openMqttProtocol
     */
    public function setOpenMqttProtocol(bool $openMqttProtocol)
    {
        $this->openMqttProtocol = $openMqttProtocol;
    }

    /**
     * @return bool
     */
    public function isOpenWebsocketCloseFrame()
    {
        return $this->openWebsocketCloseFrame ?? false;
    }

    /**
     * @param bool $openWebsocketCloseFrame
     */
    public function setOpenWebsocketCloseFrame(bool $openWebsocketCloseFrame)
    {
        $this->openWebsocketCloseFrame = $openWebsocketCloseFrame;
    }

    /**
     * @return bool
     */
    public function isSslVerifyPeer()
    {
        return $this->sslVerifyPeer ?? false;
    }

    /**
     * @param bool $sslVerifyPeer
     */
    public function setSslVerifyPeer(bool $sslVerifyPeer)
    {
        $this->sslVerifyPeer = $sslVerifyPeer;
    }

    /**
     * @return bool
     */
    public function isEnableReusePort()
    {
        return $this->enableReusePort ?? false;
    }

    /**
     * @param bool $enableReusePort
     */
    public function setEnableReusePort(bool $enableReusePort)
    {
        $this->enableReusePort = $enableReusePort;
    }

    /**
     * @return bool
     */
    public function isEnableDelayReceive()
    {
        return $this->enableDelayReceive ?? false;
    }

    /**
     * @param bool $enableDelayReceive
     */
    public function setEnableDelayReceive(bool $enableDelayReceive)
    {
        $this->enableDelayReceive = $enableDelayReceive;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param int $port
     */
    public function setPort(int $port)
    {
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host)
    {
        $this->host = $host;
    }

    /**
     * @return int
     */
    public function getSockType()
    {
        return $this->sockType;
    }

    /**
     * @param int $sockType
     */
    public function setSockType(int $sockType)
    {
        $this->sockType = $sockType;
    }

    /**
     * @return bool
     */
    public function isEnableSsl()
    {
        return $this->enableSsl ?? false;
    }

    /**
     * @inheritDoc
     * @param bool $enableSsl
     */
    public function setEnableSsl(bool $enableSsl)
    {
        $this->enableSsl = $enableSsl;
    }

    /**
     * Get ssl method
     *
     * @return string
     */
    public function getSslMethod()
    {
        return $this->sslMethod;
    }

    /**
     * Set SSL method
     *
     * @param string $sslMethod
     */
    public function setSslMethod(string $sslMethod)
    {
        $this->sslMethod = $sslMethod;
    }

    /**
     * @return string
     */
    public function getSslClientCertFile()
    {
        return $this->sslClientCertFile;
    }

    /**
     * Set SSL client cert file
     *
     * @param string $sslClientCertFile
     */
    public function setSslClientCertFile(string $sslClientCertFile)
    {
        $this->sslClientCertFile = $sslClientCertFile;
    }

    /**
     * Get swoole sock type
     *
     * @return int
     * @throws ConfigException
     */
    public function getSwooleSockType()
    {
        ConfigException::AssertNull($this, "sockType", $this->getSockType());
        if ($this->isEnableSsl()) {
            return $this->getSockType() | self::SWOOLE_SSL;
        } else {
            return $this->getSockType();
        }
    }

    /**
     * @return bool
     */
    public function isCustomHandShake()
    {
        return $this->customHandShake;
    }

    /**
     * @param bool $customHandShake
     */
    public function setCustomHandShake(bool $customHandShake)
    {
        $this->customHandShake = $customHandShake;
    }


    /**
     * Build config
     *
     * @return array
     * @throws ConfigException
     */
    public function buildConfig(): array
    {
        $build = [];
        ConfigException::AssertNull($this, "host", $this->getHost());
        ConfigException::AssertNull($this, "port", $this->getPort());
        ConfigException::AssertNull($this, "sockType", $this->getSockType());
        if ($this->isEnableSsl()) {
            ConfigException::AssertNull($this, "sslCertFile", $this->getSslCertFile());
            $build['ssl_cert_file'] = $this->getSslCertFile();
            if ($this->getSslCiphers() != null) {
                $build['ssl_ciphers'] = $this->getSslCiphers();
            }
            if ($this->getSslMethod() != null) {
                $build['ssl_method'] = $this->getSslMethod();
            }
        }
        $build['backlog'] = $this->getBacklog();
        $build['open_tcp_nodelay'] = $this->isOpenTcpNodelay();
        $build['tcp_fastopen'] = $this->isTcpFastopen();
        $build['enable_delay_receive'] = $this->isEnableDelayReceive();
        if ($this->getTcpDeferAccept() != null) {
            $build['tcp_defer_accept'] = $this->getTcpDeferAccept();
        }
        if ($this->isSslVerifyPeer()) {
            ConfigException::AssertNull($this, "sslClientCertFile", $this->getSSlClientCertFile());
            $build['ssl_verify_peer'] = $this->isSslVerifyPeer();
            $build['ssl_client_cert_file'] = $this->getSSlClientCertFile();
        }
        if (!$this->isOpenHttpProtocol() &&
            !$this->isOpenWebsocketProtocol() &&
            !$this->isOpenMqttProtocol() &&
            !$this->isOpenEofCheck() &&
            !$this->isOpenEofSplit() &&
            !$this->isOpenLengthCheck()) {
            throw new ConfigException("PortConfig中没有指定端口协议");
        }
        $count = 0;
        if ($this->isOpenHttpProtocol()) {
            $count++;
            $build['open_http_protocol'] = $this->isOpenHttpProtocol();
        }
        if ($this->isOpenWebsocketProtocol()) {
            $count++;
            $build['open_websocket_protocol'] = $this->isOpenWebsocketProtocol();
            $build['open_websocket_close_frame'] = $this->isOpenWebsocketCloseFrame();
        }
        if ($this->isOpenMqttProtocol()) {
            $count++;
            $build['open_mqtt_protocol'] = $this->isOpenMqttProtocol();
        }
        if ($this->isOpenEofCheck()) {
            $count++;
            $build['open_eof_check'] = $this->isOpenEofCheck();
            ConfigException::AssertNull($this, "packageEof", $this->getPackageEof());
            $build['package_eof'] = $this->getPackageEof();
        }
        if ($this->isOpenEofSplit()) {
            $count++;
            $build['open_eof_split'] = $this->isOpenEofSplit();
            ConfigException::AssertNull($this, "packageEof", $this->getPackageEof());
            $build['package_eof'] = $this->getPackageEof();
        }
        if ($this->isOpenLengthCheck()) {
            $count++;
            $build['open_length_check'] = $this->isOpenLengthCheck();
            ConfigException::AssertNull($this, "packageLengthOffset", $this->getPackageLengthOffset());
            $build['package_length_offset'] = $this->getPackageLengthOffset();
            ConfigException::AssertNull($this, "packageLengthType", $this->getPackageLengthType());
            $build['package_length_type'] = $this->getPackageLengthType();
            ConfigException::AssertNull($this, "packageBodyOffset", $this->getPackageBodyOffset());
            $build['package_body_offset'] = $this->getPackageBodyOffset();
            if ($this->getPackageMaxLength() != null && $this->getPackageMaxLength() > 0) {
                $build['package_max_length'] = $this->getPackageMaxLength();
            }
        }
        if ($count > 1) {
            throw new ConfigException("PortConfig中只能指定一种协议");
        }
        if ($this->isEnableReusePort()) {
            $build['enable_reuse_port'] = $this->isEnableReusePort();
        }
        return $build;
    }

    /**
     * 获取类型名称
     */
    public function getTypeName()
    {
        if ($this->isOpenWebsocketProtocol()) {
            return "WebSocket";
        }
        if ($this->isOpenHttpProtocol()) {
            return "HTTP";
        }
        if ($this->isOpenMqttProtocol()) {
            return "MQTT";
        }
        if ($this->getSwooleSockType() == self::SWOOLE_SOCK_UDP || $this->getSwooleSockType() == self::SWOOLE_SOCK_UDP6) {
            return "UDP";
        } else {
            if ($this->isOpenEofSplit() || $this->isOpenEofCheck()) {
                return "TCP-EOF";
            } else if ($this->isOpenLengthCheck()) {
                return "TCP-Length";
            } else {
                return "TCP";
            }
        }
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getPortClass()
    {
        return $this->portClass;
    }

    /**
     * @param string $portClass
     */
    public function setPortClass(string $portClass): void
    {
        $this->portClass = $portClass;
    }

    /**
     * @return int
     */
    public function getWsOpcode(): int
    {
        return $this->wsOpcode;
    }

    /**
     * @param int $wsOpcode
     */
    public function setWsOpcode(int $wsOpcode): void
    {
        $this->wsOpcode = $wsOpcode;
    }

    /**
     * 获取基础类型
     * @return string
     * @throws ConfigException
     */
    public function getBaseType(): string
    {
        if ($this->isOpenHttpProtocol()) {
            return "http";
        }
        if ($this->isOpenWebsocketProtocol()) {
            return "ws";
        }
        if ($this->isOpenMqttProtocol()) {
            return "mqtt";
        }
        if ($this->getSwooleSockType() == self::SWOOLE_SOCK_TCP || $this->getSwooleSockType() == self::SWOOLE_SOCK_TCP6) {
            return "tcp";
        }
        if ($this->getSwooleSockType() == self::SWOOLE_SOCK_UDP || $this->getSwooleSockType() == self::SWOOLE_SOCK_UDP6) {
            return "udp";
        }
        return "unknown";
    }
}