<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Go;

use ESD\Core\Server\Beans\Request;
use ESD\Core\Server\Beans\Response;
use ESD\Core\Server\Beans\WebSocketFrame;
use ESD\Core\Server\Port\ServerPort;

/**
 * Class GoPort
 * @package ESD\Go
 */
class GoPort extends ServerPort
{
    /**
     * @param int $fd
     * @param int $reactorId
     * @return mixed|void
     */
    public function onTcpConnect(int $fd, int $reactorId)
    {
        // TODO: Implement onTcpConnect() method.
    }

    /**
     * @param int $fd
     * @param int $reactorId
     */
    public function onTcpClose(int $fd, int $reactorId)
    {
        // TODO: Implement onTcpClose() method.
    }

    /**
     * @param int $fd
     * @param int $reactorId
     * @param string $data
     */
    public function onTcpReceive(int $fd, int $reactorId, string $data)
    {
        // TODO: Implement onTcpReceive() method.
    }

    /**
     * @param string $data
     * @param array $client_info
     */
    public function onUdpPacket(string $data, array $client_info)
    {
        // TODO: Implement onUdpPacket() method.
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed|void
     */
    public function onHttpRequest(Request $request, Response $response)
    {

    }

    /**
     * @param WebSocketFrame $frame
     * @return mixed|void
     */
    public function onWsMessage(WebSocketFrame $frame)
    {
        // TODO: Implement onWsMessage() method.
    }

    /**
     * @param Request $request
     * @return mixed|void
     */
    public function onWsOpen(Request $request)
    {
        // TODO: Implement onWsOpen() method.
    }

    /**
     * @param Request $request
     * @return bool|void
     */
    public function onWsPassCustomHandshake(Request $request): bool
    {
        // TODO: Implement onWs
    }

    /**
     * @param int $fd
     * @param int $reactorId
     */
    public function onWsClose(int $fd, int $reactorId)
    {
        // TODO: Implement onWsClose() method.
    }
}