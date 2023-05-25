<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Pack\Aspect;

use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Core\Server\Beans\Request;
use ESD\Server\Coroutine\Server;
use ESD\Plugins\Aop\OrderAspect;
use ESD\Plugins\Pack\ClientData;
use ESD\Plugins\Pack\PackConfig;
use ESD\Plugins\Pack\PackTool\IPack;
use ESD\Goaop\Go\Aop\Intercept\MethodInvocation;
use ESD\Goaop\Go\Lang\Annotation\Around;

/**
 * Class PackAspect
 * @package ESD\Plugins\Pack\Aspect
 */
class PackAspect extends OrderAspect
{
    use GetLogger;
    /**
     * @var PackConfig[]
     */
    protected $packConfigs;
    /**
     * @var IPack[]
     */
    protected $packTools = [];

    /**
     * RouteAspect constructor.
     * @param $packConfigs
     * @throws \Exception
     */
    public function __construct($packConfigs)
    {
        $this->packConfigs = $packConfigs;
        foreach ($this->packConfigs as $packConfig) {
            if (!empty($packConfig->getPackTool())) {
                if (!isset($this->packTools[$packConfig->getPackTool()])) {
                    $className = $packConfig->getPackTool();
                    $this->packTools[$packConfig->getPackTool()] = DIget($className);
                }
            }
        }
    }

    /**
     * around onHttpRequest
     *
     * @param MethodInvocation $invocation Invocation
     * @throws \Throwable
     * @Around("within(ESD\Core\Server\Port\IServerPort+) && execution(public **->onHttpRequest(*))")
     */
    protected function aroundHttpRequest(MethodInvocation $invocation)
    {
        /**
         * @var $request Request
         */
        list($request, $response) = $invocation->getArguments();
        $clientData = new ClientData($request->getFd(),
            $request->getMethod(),
            $request->getUri()->getPath(),
            $request->getBody()->getContents());
        $clientData->setRequest($request);
        $clientData->setResponse($response);
        setContextValue("ClientData", $clientData);
        $invocation->proceed();
        return;
    }

    /**
     * around onTcpReceive
     *
     * @param MethodInvocation $invocation Invocation
     * @throws \Throwable
     * @Around("within(ESD\Core\Server\Port\IServerPort+) && execution(public **->onTcpReceive(*))")
     */
    protected function aroundTcpReceive(MethodInvocation $invocation)
    {
        list($fd, $reactorId, $data) = $invocation->getArguments();
        $abstractServerPort = $invocation->getThis();
        $packConfig = $this->packConfigs[$abstractServerPort->getPortConfig()->getPort()];
        $packTool = $this->packTools[$packConfig->getPackTool()];

        $clientData = $packTool->unPack($fd, $data, $packConfig);
        if ($clientData == null) {
            return;
        }
        setContextValue("ClientData", $clientData);
        $invocation->proceed();
        return;
    }

    /**
     * around onWsMessage
     *
     * @param MethodInvocation $invocation Invocation
     * @throws \Throwable
     * @Around("within(ESD\Core\Server\Port\IServerPort+) && execution(public **->onWsMessage(*))")
     */
    protected function aroundWsMessage(MethodInvocation $invocation)
    {
        list($frame) = $invocation->getArguments();
        $abstractServerPort = $invocation->getThis();
        $packConfig = $this->packConfigs[$abstractServerPort->getPortConfig()->getPort()];
        $packTool = $this->packTools[$packConfig->getPackTool()];
        $clientData = $packTool->unPack($frame->getFd(), $frame->getData(), $packConfig);
        if ($clientData == null) {
            return;
        }
        setContextValue("ClientData", $clientData);
        $invocation->proceed();
        return;
    }

    /**
     * around onUdpPacket
     *
     * @param MethodInvocation $invocation Invocation
     * @Around("within(ESD\Core\Server\Port\IServerPort+) && execution(public **->onUdpPacket(*))")
     * @throws \Throwable
     */
    protected function aroundUdpPacket(MethodInvocation $invocation)
    {
        list($data, $clientInfo) = $invocation->getArguments();
        $abstractServerPort = $invocation->getThis();
        $packConfig = $this->packConfigs[$abstractServerPort->getPortConfig()->getPort()];
        $packTool = $this->packTools[$packConfig->getPackTool()];
        $clientData = $packTool->unPack(-1, $data, $packConfig);
        if ($clientData == null) {
            return;
        }
        $clientData->setUdpClientInfo($clientInfo);
        setContextValue("ClientData", $clientData);
        $invocation->proceed();
        return;
    }

    /**
     * Enhanced send, which can be transcoded and sent according to different protocols
     *
     * @param $fd
     * @param $data
     * @param null $topic
     * @return bool
     */
    public function autoBoostSend($fd, $data, $topic = null): bool
    {
        if ($data == null) {
            return false;
        }
        $clientInfo = Server::$instance->getClientInfo($fd);
        $packConfig = $this->packConfigs[$clientInfo->getServerPort()] ?? null;
        if ($packConfig == null) {
            return false;
        }

        $pack = $this->packTools[$packConfig->getPackTool()];
        $data = $pack->pack($data, $packConfig, $topic);

        if (Server::$instance->isEstablished($fd)) {
            return Server::$instance->wsPush($fd, $data, $packConfig->getWsOpcode());
        } else {
            return Server::$instance->send($fd, $data);
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return "PackAspect";
    }
}