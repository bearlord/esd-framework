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
    public function redirect($url, $http_code = 302)
    {
        return $this->response->redirect($url, $http_code);
    }

    /**
     * @param string|null $title
     * @param string|null $info
     * @return string
     */
    private function msg(?string $title = 'System Message', ?string $info = null): string
    {
        return '<!DOCTYPE html><html>' .
            '<head><title>' . $title . '</title><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/></head>' .
            '<body><h1>' . $title . '</h1><h2>' . $info . '</h2></body></html>';
    }

    /**
     * @param \Throwable $exception
     * @return false|mixed|string
     * @throws \Throwable
     */
    public function onExceptionHandle(\Throwable $exception)
    {
        if ($this->clientData->getResponse() != null) {
            switch (true) {
                case ($exception instanceof AccessDeniedException):
                    $status = 401;
                    break;

                case ($exception instanceof AlertResponseException):
                    $status = 500;
                    break;

                case ($exception instanceof RouteException):
                case ($exception instanceof ResponseException):
                default:
                    $status = 200;
            }
            $this->response->withStatus($status);

            $contentType = $this->request->getContentType();
            if (strpos($contentType, 'application/json') !== false) {
                $content = json_encode([
                    'code' => $exception->getCode(),
                    'message' => $exception->getMessage(),
                    'data' => new \stdClass()
                ]);
                $this->response->withHeader('Content-Type', $contentType);
            } else {
                $content = $this->msg('System Message', $exception->getMessage());
            }

            return $content;
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
