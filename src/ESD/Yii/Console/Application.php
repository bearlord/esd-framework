<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Yii\Console;

use DI\Container;
use ESD\Core\DI\DI;
use ESD\Core\Server\Server;
use ESD\Core\Server\Beans\Request;
use ESD\Core\Server\Beans\Response;
use ESD\Plugins\EasyRoute\EasyRoutePlugin;
use ESD\Plugins\Session\HttpSession;
use ESD\Yii\Base\Action;
use ESD\Yii\Base\Controller;
use ESD\Yii\Base\InvalidConfigException;
use ESD\Yii\Base\InvalidParamException;
use ESD\Yii\Base\InvalidRouteException;
use ESD\Yii\Di\ServiceLocator;
use ESD\Yii\Plugin\Mongodb\MongodbPools;
use ESD\Yii\Yii;
use ESD\Yii\Db\Connection;
use ESD\Yii\Plugin\Pdo\PdoPools;
use FastRoute\Dispatcher;

/**
 * Class Application
 * @package \ESD\Yii\Base
 * @property \ESD\Core\Server\Beans\Request $request The request component. This property is read-only.
 * @property \ESD\Core\Server\Beans\Response $response The response component. This property is read-only.
 * @property \ESD\Plugins\Session\HttpSession $session The session component. This property is read-only.
 * @property \ESD\Yii\Web\User $user The user component. This property is read-only.
 * @property \ESD\Yii\Caching\Cache $cache The cache application component. Null if the component is not enabled.
 * @property \ESD\Yii\Base\Security $security The security application component. This property is read-only.
 */
class Application extends \ESD\Yii\Base\Application
{
    /**
     * The option name for specifying the application configuration file path.
     */
    const OPTION_APPCONFIG = 'appconfig';

    /**
     * @var static[] static instances in format: `[className => object]`
     */
    private static $_instances = [];

    /**
     * @var array mapping from controller ID to controller configurations.
     * Each name-value pair specifies the configuration of a single controller.
     * A controller configuration can be either a string or an array.
     * If the former, the string should be the fully qualified class name of the controller.
     * If the latter, the array must contain a `class` element which specifies
     * the controller's fully qualified class name, and the rest of the name-value pairs
     * in the array are used to initialize the corresponding controller properties. For example,
     *
     * ```php
     * [
     *   'account' => 'app\controllers\UserController',
     *   'article' => [
     *      'class' => 'app\controllers\PostController',
     *      'pageTitle' => 'something new',
     *   ],
     * ]
     * ```
     */
    public $controllerMap = [];

    /**
     * @var string the default route of this application. Defaults to 'help',
     * meaning the `help` command.
     */
    public $defaultRoute = 'help';

    /**
     * @var bool whether to enable the commands provided by the core framework.
     * Defaults to true.
     */
    public $enableCoreCommands = true;

    /**
     * @var string the requested route
     */
    public $requestedRoute;
    /**
     * @var Action the requested Action. If null, it means the request cannot be resolved into an action.
     */
    public $requestedAction;
    /**
     * @var array the parameters supplied to the requested action.
     */
    public $requestedParams;

    /**
     * Returns static class instance, which can be used to obtain meta information.
     * @param bool $refresh whether to re-create static instance even, if it is already cached.
     * @return static class instance.
     * @throws InvalidConfigException
     */
    public static function instance($refresh = false)
    {
        $className = get_called_class();
        if ($refresh || !isset(self::$_instances[$className])) {
            self::$_instances[$className] = Yii::createObject($className);
        }
        return self::$_instances[$className];
    }

    /**
     * Prepare init
     */
    public function preInit()
    {
        $config = Server::$instance->getConfigContext()->get('yii');

        //Set base path
        $srcDir = Server::$instance->getServerConfig()->getSrcDir();
        $this->setBasePath($srcDir);

        //Set vendor path
        $vendorPath =  realpath(dirname($srcDir) . '/vendor');
        $this->setVendorPath($vendorPath);

        //Set web path
        if (Server::$instance->getServerConfig()->isEnableStaticHandler()) {
            $documentRoot = Server::$instance->getServerConfig()->getDocumentRoot();
            if (empty($documentRoot)) {
                $documentRoot = realpath(dirname($srcDir) . '/web');
            }
            $this->setWebPath(Server::$instance->getServerConfig()->getDocumentRoot());
        }

        //Set language
        if (!empty($config['language'])) {
            $this->setLanguage($config['language']);
        }

        //Merge core components with custom components
        $newConfig = $config;
        unset($newConfig['db']);

        foreach ($this->coreComponents() as $id => $component) {
            if (!isset($newConfig['components'][$id])) {
                $newConfig['components'][$id] = $component;
            } elseif (is_array($newConfig['components'][$id]) && !isset($newConfig['components'][$id]['class'])) {
                $newConfig['components'][$id]['class'] = $component['class'];
            }
        }

        $this->setComponents($newConfig['components']);
        unset($newConfig);

        if ($this->enableCoreCommands) {
            foreach ($this->coreCommands() as $id => $command) {
                if (!isset($this->controllerMap[$id])) {
                    $this->controllerMap[$id] = $command;
                }
            }
        }
    }

    /**
     * Handles the specified request.
     * @param Request $request the request to be handled
     * @return Response the resulting response
     * @throws Exception
     */
    public function handleRequest($request)
    {
        list($route, $params) = $request->resolve();
        $this->requestedRoute = $route;
        $result = $this->runAction($route, $params);
        if ($result instanceof Response) {
            return $result;
        }

        $response = $this->getResponse();
        $response->exitStatus = $result;

        return $response;
    }

    /**
     * @param $route
     * @return array|false
     * @throws InvalidConfigException
     */
    public function createController($route)
    {
        // double slashes or leading/ending slashes may cause substr problem
        $route = trim($route, '/');
        if (strpos($route, '//') !== false) {
            return false;
        }

        if (strpos($route, '/') !== false) {
            list($id, $route) = explode('/', $route, 2);
        } else {
            $id = $route;
            $route = '';
        }

        // module and controller map take precedence
        if (isset($this->controllerMap[$id])) {
            $controller = Yii::createObject($this->controllerMap[$id], [$id, $this]);
            return [$controller, $route];
        }

        $controller = $this->createControllerByID($id);
        if ($controller === null && $route !== '') {
            $controller = $this->createControllerByID($id . '/' . $route);
            $route = '';
        }

        return $controller === null ? false : [$controller, $route];
    }

    /**
     * @var string
     */
    private $appControllerNamespace = "App\Console";

    /**
     * Creates a controller based on the given controller ID.
     *
     * The controller ID is relative to this module. The controller class
     * should be namespaced under [[controllerNamespace]].
     *
     * Note that this method does not check [[modules]] or [[controllerMap]].
     *
     * @param string $id the controller ID.
     * @return Controller|null the newly created controller instance, or `null` if the controller ID is invalid.
     * @throws InvalidConfigException if the controller class and its file name do not match.
     * This exception is only thrown when in debug mode.
     */
    public function createControllerByID($id)
    {
        $pos = strrpos($id, '/');
        if ($pos === false) {
            $prefix = '';
            $className = $id;
        } else {
            $prefix = substr($id, 0, $pos + 1);
            $className = substr($id, $pos + 1);
        }

        if ($this->isIncorrectClassNameOrPrefix($className, $prefix)) {
            return null;
        }

        $className = preg_replace_callback('%-([a-z0-9_])%i', function ($matches) {
                return ucfirst($matches[1]);
            }, ucfirst($className)) . 'Controller';
        $className = ltrim($this->appControllerNamespace . '\\' . str_replace('/', '\\', ucfirst($prefix)) . $className, '\\');

        if (strpos($className, '-') !== false || !class_exists($className)) {
            return null;
        }

        
        if (is_subclass_of($className, 'ESD\Yii\Console\Controller')) {
            $controller = Yii::createObject($className, [
                $id,
                $this
            ]);
            return get_class($controller) === $className ? $controller : null;
        } elseif (true) {
            throw new InvalidConfigException("Controller class must extend from \\ESD\Yii\\Console\\Controller.");
        } else {
            return null;
        }
    }

    /**
     * Checks if class name or prefix is incorrect
     *
     * @param string $className
     * @param string $prefix
     * @return bool
     */
    private function isIncorrectClassNameOrPrefix($className, $prefix)
    {
        if (!preg_match('%^[a-z][a-z0-9\\-_]*$%', $className)) {
            return true;
        }
        if ($prefix !== '' && !preg_match('%^[a-z0-9_/]+$%i', $prefix)) {
            return true;
        }

        return false;
    }

    /**
     * Run route
     *
     * @param $route
     * @return mixed
     * @throws InvalidConfigException
     */
    public function runRoute($route)
    {
        $controller = $this->createController($route);
        if (!empty($controller)) {
            return call_user_func([$controller[0], $controller[1]]);
        }
    }

    /**
     * Runs a controller action specified by a route.
     * This method parses the specified route and creates the corresponding child module(s), controller and action
     * instances. It then calls [[Controller::runAction()]] to run the action with the given parameters.
     * If the route is empty, the method will use [[defaultRoute]].
     *
     * For example, to run `public function actionTest($a, $b)` assuming that the controller has options the following
     * code should be used:
     *
     * ```php
     * \Yii::$app->runAction('controller/test', ['option' => 'value', $a, $b]);
     * ```
     *
     * @param string $route the route that specifies the action.
     * @param array $params the parameters to be passed to the action
     * @return int|Response the result of the action. This can be either an exit code or Response object.
     * Exit code 0 means normal, and other values mean abnormal. Exit code of `null` is treaded as `0` as well.
     * @throws Exception if the route is invalid
     */
    public function runAction($route, $params = [])
    {
        try {
            $parts = $this->createController($route);
            if (is_array($parts)) {
                /* @var $controller \ESD\Yii\Console\Controller */
                list($controller, $actionID) = $parts;
                $res = $controller->runAction($actionID, $params);
                return is_object($res) ? $res : (int) $res;
            }
        } catch (InvalidRouteException $e) {
            throw new UnknownCommandException($route, $this, 0, $e);
        }
    }

    /**
     * Returns the configuration of core application components.
     * @see set()
     */
    public function coreComponents()
    {
        return array_merge(parent::coreComponents(), [
            'request' => ['class' => '\ESD\Yii\Console\Request'],
            'response' => ['class' => '\ESD\Yii\Console\Response']
        ]);
    }

    /**
     * Returns the configuration of the built-in commands.
     * @return array the configuration of the built-in commands.
     */
    public function coreCommands()
    {
        return [
            'cache' => 'ESD\Yii\Console\Controllers\CacheController',
        ];
    }
}