<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\EasyRoute\Controller;

use DI\Annotation\Inject;
use ESD\Core\ParamException;
use ESD\Core\Server\Beans\Request;
use ESD\Core\Server\Beans\Response;
use ESD\Plugins\EasyRoute\MethodNotAllowedException;
use ESD\Plugins\EasyRoute\RouteException;
use ESD\Plugins\Pack\ClientData;
use ESD\Yii\Base\ActionEvent;
use ESD\Yii\Base\Controller;
use Psr\Log\LoggerInterface;

/**
 * Class EasyController
 * @package ESD\Plugins\EasyRoute\Controller
 */
abstract class EasyController extends Controller implements IController
{
    /**
     * @event ActionEvent an event raised right before executing a controller action.
     * You may set [[ActionEvent::isValid]] to be false to cancel the action execution.
     */
    const EVENT_BEFORE_ACTION = 'beforeAction';
    /**
     * @event ActionEvent an event raised right after executing a controller action.
     */
    const EVENT_AFTER_ACTION = 'afterAction';

    /**
     * @Inject()
     * @var Request
     */
    protected $request;

    /**
     * @Inject()
     * @var Response
     */
    protected $response;

    /**
     * @Inject()
     * @var ClientData
     */
    protected $clientData;

    /**
     * @Inject()
     * @var LoggerInterface
     */
    protected $log;

    /**
     * @inheritDoc
     * @param string|null $controllerName
     * @param string|null $methodName
     * @param array|null $params
     * @return mixed
     * @throws \Throwable
     */
    public function handle(?string $controllerName, ?string $methodName, ?array $params)
    {
        if (!is_callable([$this, $methodName]) || $methodName == null) {
            $callMethodName = 'defaultMethod';
        } else {
            $callMethodName = $methodName;
        }
        try {
            $action = $this->createAction($methodName);
            
            $result = null;
            if ($this->beforeAction($action)) {
                // run the action
                $result = $action->runWithParams($params);
                $result = $this->afterAction($action, $result);
            }
            return $result;
        } catch (\Throwable $exception) {
            setContextValue("lastException", $exception);
            return $this->onExceptionHandle($exception);
        }
    }

    /**
     * Called on every request
     *
     * @param string|null $controllerName
     * @param string|null $methodName
     * @return mixed
     */
    public function initialization(?string $controllerName, ?string $methodName)
    {

    }

    /**
     * @inheritDoc
     * @param $methodName
     * @return mixed
     */
    abstract protected function defaultMethod(?string $methodName);

    /**
     * @inheritDoc
     * @param $exception
     * @return mixed
     * @throws \Throwable
     */
    public function onExceptionHandle(\Throwable $exception)
    {
        if ($this->clientData->getResponse() != null) {
            $this->response->withStatus(404);
            $this->response->withHeader("Content-Type", "text/html;charset=UTF-8");

            if ($exception instanceof RouteException) {
                $msg = '404 Not found / ' . $exception->getMessage();
            } elseif ($exception instanceof ParamException) {
                $this->response->withStatus(400);
                $msg = '400 Bad request / ' . $exception->getMessage();
            } else if ($exception instanceof MethodNotAllowedException) {
                $this->response->withStatus(405);
                $msg = '405 method not allowed';
            } else {
                $this->response->withStatus(500);
                $msg = '500 internal server error';
            }
            return $msg;
        } else {
            return $exception->getMessage();
        }
    }

    /**
     * Refreshes the current page.
     * This method is a shortcut to [[Response::refresh()]].
     *
     * You can use it in an action by returning the [[Response]] directly:
     *
     * ```php
     * // stop executing this action and refresh the current page
     * return $this->refresh();
     * ```
     *
     * @param string $anchor the anchor that should be appended to the redirection URL.
     * Defaults to empty. Make sure the anchor starts with '#' if you want to specify it.
     * @return Response the response object itself
     */
    public function refresh(?string $anchor = ''): Response
    {
        return $this->response->redirect($this->request->getUri() . $anchor);
    }
}
