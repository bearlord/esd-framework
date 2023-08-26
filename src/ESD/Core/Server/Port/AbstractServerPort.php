<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Server\Port;

use ESD\Core\Context\Context;
use ESD\Core\Exception;
use ESD\Core\ParamException;
use ESD\Core\Server\Beans\AbstractRequest;
use ESD\Core\Server\Beans\AbstractResponse;
use ESD\Core\Server\Beans\Request;
use ESD\Core\Server\Beans\Response;
use ESD\Core\Server\Beans\WebSocketCloseFrame;
use ESD\Core\Server\Beans\WebSocketFrame;
use ESD\Core\Server\Config\PortConfig;
use ESD\Core\Server\Server;
use ESD\Go\GoController;

/**
 * AbstractServerPort
 *
 * Class ServerPort
 * @package ESD\Core\Server
 */
abstract class AbstractServerPort
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var PortConfig
     */
    private $portConfig;

    /**
     * @var Server
     */
    private $server;

    /**
     * @var \Swoole\Server\Port
     */
    private $swoolePort;

    /**
     * AbstractServerPort constructor.
     *
     * @param Server $server
     * @param PortConfig $portConfig
     */
    public function __construct(Server $server, PortConfig $portConfig)
    {
        $this->portConfig = $portConfig;
        $this->server = $server;
        $this->context = $this->server->getContext();
    }

    /**
     * @return mixed
     */
    public function getSwoolePort()
    {
        return $this->swoolePort;
    }

    /**
     * Create port
     *
     * @throws \ESD\Core\Plugins\Config\ConfigException
     */
    public function create(): void
    {
        if ($this->server->getMainPort() == $this) {
            //Swoole create port, get port instance
            $this->swoolePort = $this->server->getServer()->ports[0];
            //Listening is server
            $listening = $this->server->getServer();
        } else {
            $configData = $this->getPortConfig()->buildConfig();
            $this->swoolePort = $this->server->getServer()->listen($this->getPortConfig()->getHost(),
                $this->getPortConfig()->getPort(),
                $this->getPortConfig()->getSwooleSockType());
            $this->swoolePort->set($configData);
            //Listening is port instance
            $listening = $this->swoolePort;
        }

        //TCP
        if ($this->isTcp()) {
            $listening->on("connect", [$this, "_onConnect"]);
            $listening->on("close", [$this, "_onClose"]);
            $listening->on("receive", [$this, "_onReceive"]);
        }

        //UDP
        if ($this->isUDP()) {
            $listening->on("packet", [$this, "_onPacket"]);
        }

        //HTTP
        if ($this->isHttp()) {
            $listening->on("request", [$this, "_onRequest"]);
        }

        //WebSocket
        if ($this->isWebSocket()) {
            $listening->on("close", [$this, "_onClose"]);
            $listening->on("message", [$this, "_onMessage"]);
            $listening->on("open", [$this, "_onOpen"]);
            if ($this->getPortConfig()->isCustomHandShake()) {
                $listening->on("handshake", [$this, "_onHandshake"]);
            }
        }
    }

    /**
     * @return PortConfig
     */
    public function getPortConfig(): PortConfig
    {
        return $this->portConfig;
    }

    /**
     * Is TCP
     *
     * @return bool
     */
    public function isTcp(): bool
    {
        if ($this->isHttp()) return false;
        if ($this->isWebSocket()) return false;
        if ($this->getPortConfig()->getSockType() == PortConfig::SWOOLE_SOCK_TCP ||
            $this->getPortConfig()->getSockType() == PortConfig::SWOOLE_SOCK_TCP6) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Is HTTP
     *
     * @return bool
     */
    public function isHttp(): bool
    {
        return $this->getPortConfig()->isOpenHttpProtocol() || $this->getPortConfig()->isOpenWebsocketProtocol();
    }

    /**
     * Is websocket
     *
     * @return bool
     */
    public function isWebSocket(): bool
    {
        return $this->getPortConfig()->isOpenWebsocketProtocol();
    }

    /**
     * Is UDP
     *
     * @return bool
     */
    public function isUDP(): bool
    {
        if ($this->getPortConfig()->getSockType() == PortConfig::SWOOLE_SOCK_UDP ||
            $this->getPortConfig()->getSockType() == PortConfig::SWOOLE_SOCK_UDP6) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $server
     * @param int $fd
     * @param int $reactorId
     */
    public function _onConnect($server, int $fd, int $reactorId)
    {
        Server::$instance->getProcessManager()->getCurrentProcess()->waitReady();
        try {
            $this->onTcpConnect($fd, $reactorId);
        } catch (\Throwable $e) {
            Server::$instance->getLog()->error($e);
        }
    }

    /**
     * @param int $fd
     * @param int $reactorId
     * @return mixed
     */
    public abstract function onTcpConnect(int $fd, int $reactorId);

    /**
     * @param $server
     * @param int $fd
     * @param int $reactorId
     */
    public function _onClose($server, int $fd, int $reactorId)
    {
        Server::$instance->getProcessManager()->getCurrentProcess()->waitReady();
        try {
            $port = Server::$instance->getPortManager()->getPortFromFd($fd);
            if (Server::$instance->isEstablished($fd)) {
                $this->onWsClose($fd, $reactorId);
            } else if ($port->isTcp()) {
                $this->onTcpClose($fd, $reactorId);
            }
        } catch (\Throwable $e) {
            Server::$instance->getLog()->error($e);
        }
    }

    public abstract function onTcpClose(int $fd, int $reactorId);

    public abstract function onWsClose(int $fd, int $reactorId);

    public function _onReceive($server, int $fd, int $reactorId, string $data)
    {
        Server::$instance->getProcessManager()->getCurrentProcess()->waitReady();
        try {
            $this->onTcpReceive($fd, $reactorId, $data);
        } catch (\Throwable $e) {
            Server::$instance->getLog()->error($e);
        }
    }

    public abstract function onTcpReceive(int $fd, int $reactorId, string $data);

    /**
     * @param $server
     * @param string $data
     * @param array $clientInfo
     */
    public function _onPacket($server, string $data, array $clientInfo)
    {
        Server::$instance->getProcessManager()->getCurrentProcess()->waitReady();
        try {
            $this->onUdpPacket($data, $clientInfo);
        } catch (\Throwable $e) {
            Server::$instance->getLog()->error($e);
        }
    }

    public abstract function onUdpPacket(string $data, array $clientInfo);

    /**
     * @param $request
     * @param $response
     * @throws \Exception
     */
    public function _onRequest($request, $response)
    {
        Server::$instance->getProcessManager()->getCurrentProcess()->waitReady();

        /**
         * @var $_response Response
         */
        $_response = DIGet(AbstractResponse::class);
        $_response->load($response);

        /**
         * @var $_request Request
         */
        $_request = DIGet(AbstractRequest::class);
        try {
            $_request->load($request);
        } catch (ParamException $exception) {
            Server::$instance->getLog()->error($exception->getMessage());

            $msg = '400 Bad Request';
            $_response->withStatus(400)->withContent($msg)->end();
            return false;
        } catch (Exception $exception) {
            Server::$instance->getLog()->error($exception->getMessage());
            return false;
        }

        try {
            setContextValueWithClass("request", $_request, Request::class);
            setContextValueWithClass("response", $_response, Response::class);
            $this->onHttpRequest($_request, $_response);
        } catch (\Throwable $e) {
            Server::$instance->getLog()->error($e);
        }
        $_response->end();
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    public abstract function onHttpRequest(Request $request, Response $response);

    /**
     * @param $server
     * @param $frame
     */
    public function _onMessage($server, $frame)
    {
        Server::$instance->getProcessManager()->getCurrentProcess()->waitReady();
        try {
            if (isset($frame->code)) {
                $this->onWsMessage(new WebSocketCloseFrame($frame));
            } else {
                $this->onWsMessage(new WebSocketFrame($frame));
            }
        } catch (\Throwable $e) {
            Server::$instance->getLog()->error($e);
        }
    }

    /**
     * @param WebSocketFrame $frame
     * @return mixed
     */
    public abstract function onWsMessage(WebSocketFrame $frame);

    /**
     * @param $request
     * @param $response
     * @return bool
     * @throws \Exception
     */
    public function _onHandshake($request, $response)
    {
        Server::$instance->getProcessManager()->getCurrentProcess()->waitReady();

        try {
            /**
             * @var $_request Request
             */
            $_request = DIGet(AbstractRequest::class);
            $_request->load($request);
            setContextValueWithClass("request", $_request, Request::class);
        } catch (\Throwable $e) {
            Server::$instance->getLog()->error($e);
        }

        $success = $this->onWsPassCustomHandshake($_request);
        if (!$success) {
            return false;
        }

        //Handshake connection algorithm for authentication
        $secWebSocketKey = $request->header['sec-websocket-key'];
        $patten = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
        if (0 === preg_match($patten, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
            $response->end();
            return false;
        }
        $key = base64_encode(sha1(
            $request->header['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
            true
        ));
        $headers = [
            'Upgrade' => 'websocket',
            'Connection' => 'Upgrade',
            'Sec-WebSocket-Accept' => $key,
            'Sec-WebSocket-Version' => '13',
        ];
        if (isset($request->header['sec-websocket-protocol'])) {
            $headers['Sec-WebSocket-Protocol'] = $request->header['sec-websocket-protocol'];
        }
        foreach ($headers as $key => $val) {
            $response->header($key, $val);
        }
        $response->status(101);
        $response->end();

        $this->server->defer(function () use ($request) {
            \Swoole\Coroutine::create(function () use ($request) {
                $this->_onOpen($this->server->getServer(), $request);
            });
        });
    }

    public abstract function onWsPassCustomHandshake(Request $request): bool;

    /**
     * @param $server
     * @param $request
     */
    public function _onOpen($server, $request)
    {
        Server::$instance->getProcessManager()->getCurrentProcess()->waitReady();
        try {
            /**
             * @var $_request Request
             */
            $_request = DIGet(AbstractRequest::class);
            $_request->load($request);
            setContextValueWithClass("request", $_request, Request::class);
            $this->onWsOpen($_request);
        } catch (\Throwable $e) {
            Server::$instance->getLog()->error($e);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public abstract function onWsOpen(Request $request);

}
