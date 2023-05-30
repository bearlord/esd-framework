<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Go;

use DI\Annotation\Inject;
use ESD\Core\Server\Beans\Request;
use ESD\Go\Exception\AlertResponseException;
use ESD\Go\Exception\ResponseException;
use ESD\Plugins\EasyRoute\Controller\EasyController;
use ESD\Plugins\EasyRoute\MethodNotAllowedException;
use ESD\Plugins\EasyRoute\RouteException;
use ESD\Plugins\Pack\GetBoostSend;
use ESD\Plugins\Security\AccessDeniedException;
use ESD\Plugins\Security\GetSecurity;
use ESD\Plugins\Session\HttpSession;
use ESD\Plugins\Topic\GetTopic;
use ESD\Plugins\Uid\GetUid;
use ESD\Plugins\Whoops\WhoopsConfig;

/**
 * Class GoController
 * @package ESD\Go
 */
class GoController extends EasyController
{
    use GetSecurity;
    use GetBoostSend;
    use GetUid;
    use GetTopic;

    /**
     * @Inject()
     * @var HttpSession
     */
    protected $session;

    /**
     * @Inject()
     * @var WhoopsConfig
     */
    protected $whoopsConfig;


    /**
     * @throws MethodNotAllowedException
     */
    public function assertGet()
    {
        if (strtolower($this->request->getMethod()) != "get") {
            throw new MethodNotAllowedException();
        }
    }

    /**
     * @throws MethodNotAllowedException
     */
    public function assertPost()
    {
        if (strtolower($this->request->getMethod()) != "post") {
            throw new MethodNotAllowedException();
        }
    }

    /**
     * @param string|null $has
     * @return bool
     */
    public function isGet(string $has = null): bool
    {
        if (strtolower($this->request->getMethod()) == "get") {
            if (!is_null($has)) {
                if (!is_null($this->request->query($has))) {
                    return true;
                } else {
                    return false;
                }
            }
            return true;
        }
        return false;
    }


    /**
     * @param string|null $has
     * @return bool
     */
    public function isPost(string $has = null): bool
    {
        if (strtolower($this->request->getMethod()) == "post") {
            if (!is_null($has)) {
                if (!is_null($this->request->post($has))) {
                    return true;
                } else {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isAjax(): bool
    {
        if (strtolower($this->request->getHeaderLine('x-requested-with')) == 'xmlhttprequest') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $url
     * @param int $http_code
     * @return mixed
     */
    public function redirect(string $url, int $http_code = 302)
    {
        return $this->response->redirect($url, $http_code);
    }

    /**
     * @param $data
     * @param null $url
     * @param int $wait
     * @param array $header
     * @return string
     */
    public function successResponse($data, $url = null, $wait = 3, array $header = [])
    {

        if (is_null($url) && $this->request->getServer(Request::HEADER_REFERER) != null) {
            $url = $this->request->getServer(Request::HEADER_REFERER);
        }

        if (is_array($data)) {
            if (empty($header)) {
                $this->response->withHeader('Content-type', 'application/json');
            } else {
                $this->response->withHeaders($header);
            }
            return json_encode([
                'data' => $data,
                'code' => 0
            ]);
        } else {
            if (!empty($header)) {
                $this->response->withHeaders($header);
            }
            return $this->msg('System Message', $data, $wait, $url);
        }
    }


    /**
     * @param string $msg
     * @param int $code
     * @param null $url
     * @param int $wait
     * @param array $header
     * @return false|string
     */
    public function errorResponse($msg = '', $code = 500, $url = null, $wait = 3, array $header = [])
    {

        if (is_null($url) && $this->request->getServer(Request::HEADER_REFERER) != null) {
            $url = $this->request->getServer(Request::HEADER_REFERER);
        }

        if ($this->isAjax()) {
            if (empty($header)) {
                $this->response->withHeader('Content-type', 'application/json');
            } else {
                $this->response->withHeaders($header);
            }
            return json_encode([
                'code' => $code,
                'message' => $msg,
                'data' => null
            ]);
        } else {
            if (!empty($header)) {
                $this->response->withHeaders($header);
            }
            return $this->msg('错误消息', $msg, $wait, $url);
        }
    }

    /**
     * @param string $title
     * @param $info
     * @param $wait
     * @param $url
     * @return string
     */
    private function msg(string $title = 'System Message', ?string $info = '', ?string $wait = '', ?string $url = '')
    {
        return '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/></head>' .
            '<meta http-equiv="Refresh" content="' . $wait . '; url=' . $url . '"/><body><h1>' . $title . '</h1>' .
            '<h2>' . $info . '</h2></body></html>';
    }

    /**
     * @param \Throwable $exception
     * @return false|mixed|string
     * @throws \Throwable
     */
    public function onExceptionHandle(\Throwable $exception)
    {
        if ($this->clientData->getResponse() != null) {
            $this->response->withStatus(404);
            $this->response->withHeader("Content-Type", "text/html;charset=UTF-8");
            if ($exception instanceof RouteException) {
                $msg = '404 Not found / ' . $exception->getMessage();
                return $msg;
            } else if ($exception instanceof AccessDeniedException) {
                $this->response->withStatus(401);
                $msg = '401 Access denied / ' . $exception->getMessage();
                return $msg;
            } else if ($exception instanceof ResponseException) {
                $this->response->withStatus(200);
                return $this->errorResponse($exception->getMessage(), $exception->getCode());
            } else if ($exception instanceof AlertResponseException) {
                $this->response->withStatus(500);
                return $this->errorResponse($exception->getMessage(), $exception->getCode());
            }
        }
        return parent::onExceptionHandle($exception);
    }

    /**
     * Called when no method is found
     * @param $methodName
     * @return mixed
     */
    protected function defaultMethod(?string $methodName)
    {
        return "";
    }

    /**
     * Send to uid
     *
     * @param $uid
     * @param $data
     */
    protected function sendToUid($uid, $data)
    {
        $fd = $this->getUidFd($uid);
        if ($fd !== false) {
            $this->autoBoostSend($fd, $data);
        } else {
            $this->log->warn("通过uid寻找fd不存在");
        }
    }

    /**
     * Get uid
     *
     * @return mixed
     */
    protected function getUid()
    {
        return $this->getFdUid($this->clientData->getFd());
    }
}
