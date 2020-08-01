<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Yii\Base;

use DI\Container;
use ESD\Core\DI\DI;
use ESD\Core\Server\Server;
use ESD\Core\Server\Beans\Request;
use ESD\Core\Server\Beans\Response;
use ESD\Plugins\Session\HttpSession;
use ESD\Yii\Di\ServiceLocator;
use ESD\Yii\Plugin\Mongodb\MongodbPools;
use ESD\Yii\Yii;
use ESD\Yii\Db\Connection;
use ESD\Yii\Plugin\Pdo\PdoPools;

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
class Application extends ServiceLocator
{
    /**
     * @var string the charset currently used for the application.
     */
    public $charset = 'UTF-8';
    /**
     * @var string the language that is meant to be used for end users. It is recommended that you
     * use [IETF language tags](http://en.wikipedia.org/wiki/IETF_language_tag). For example, `en` stands
     * for English, while `en-US` stands for English (United States).
     * @see sourceLanguage
     */
    public $language = 'en-US';

    /**
     * @var string the language that the application is written in. This mainly refers to
     * the language that the messages and view files are written in.
     * @see language
     */
    public $sourceLanguage = 'en-US';

    /**
     * @var string Default time zone
     */
    public $timeZone = 'Asia/Shanghai';

    /**
     * @var string Cookie validation key
     */
    public $cookieValidationKey = 'yii';

    /**
     * @var string the root directory of the application.
     */
    private $_basePath;

    /**
     * @var static[] static instances in format: `[className => object]`
     */
    private static $_instances = [];

    /**
     * Application constructor.
     */
    public function __construct()
    {
        Yii::$app = $this;
        $this->preInit();
    }

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
    }

    /**
     * Returns the root directory of the module.
     * It defaults to the directory containing the module class file.
     * @return string the root directory of the module.
     */
    public function getBasePath()
    {
        if ($this->_basePath === null) {
            $class = new \ReflectionClass($this);
            $this->_basePath = dirname($class->getFileName());
        }

        return $this->_basePath;
    }

    /**
     * Sets the root directory of the module.
     * This method can only be invoked at the beginning of the constructor.
     * @param string $path the root directory of the module. This can be either a directory name or a [path alias](guide:concept-aliases).
     * @throws InvalidParamException if the directory does not exist.
     */
    public function setBasePath($path)
    {
        $path = Yii::getAlias($path);
        $p = strncmp($path, 'phar://', 7) === 0 ? $path : realpath($path);
        if ($p !== false && is_dir($p)) {
            $this->_basePath = $p;
        } else {
            throw new InvalidParamException("The directory does not exist: $path");
        }
        Yii::setAlias('@app', $this->getBasePath());
        Yii::setAlias('@App', $this->getBasePath());
    }

    private $_vendorPath;

    /**
     * Returns the directory that stores vendor files.
     * @return string the directory that stores vendor files.
     * Defaults to "vendor" directory under [[basePath]].
     */
    public function getVendorPath()
    {
        if ($this->_vendorPath === null) {
            $this->setVendorPath($this->getBasePath() . DIRECTORY_SEPARATOR . 'vendor');
        }

        return $this->_vendorPath;
    }

    /**
     * Sets the directory that stores vendor files.
     * @param string $path the directory that stores vendor files.
     */
    public function setVendorPath($path)
    {
        $this->_vendorPath = Yii::getAlias($path);
        Yii::setAlias('@vendor', $this->_vendorPath);
        Yii::setAlias('@bower', $this->_vendorPath . DIRECTORY_SEPARATOR . 'bower-asset');
        Yii::setAlias('@npm', $this->_vendorPath . DIRECTORY_SEPARATOR . 'npm-asset');
    }

    /**
     * Sets the web and webroot path
     * @param $path
     */
    public function setWebPath($path)
    {
        Yii::setAlias('@webroot', $path);
        Yii::setAlias('@web', '/');
    }

    private $_runtimePath;

    /**
     * Returns the directory that stores runtime files.
     * @return string the directory that stores runtime files.
     * Defaults to the "runtime" subdirectory under [[basePath]].
     */
    public function getRuntimePath()
    {
        if ($this->_runtimePath === null) {
            $this->setRuntimePath($this->getBasePath() . DIRECTORY_SEPARATOR . 'runtime');
        }

        return $this->_runtimePath;
    }

    /**
     * Sets the directory that stores runtime files.
     * @param string $path the directory that stores runtime files.
     */
    public function setRuntimePath($path)
    {
        $this->_runtimePath = Yii::getAlias($path);
        Yii::setAlias('@runtime', $this->_runtimePath);
    }

    /**
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
    public function getDb($name = "default")
    {
        if ($name === "default") {
            $poolKey = "default";
            $contextKey = "Pdo:default";
        } elseif ($name === "slave") {
            $slaveConfigs = Server::$instance->getConfigContext()->get("yii.db.default.slaves");
            if (empty($slaveConfigs)) {
                $poolKey = "default";
                $contextKey = "Pdo:default";
            } else {
                $slaveRandKey = array_rand($slaveConfigs);

                $poolKey = sprintf("default.slave.%s", $slaveRandKey);
                $contextKey = sprintf("Pdo:default.slave.%s", $slaveRandKey);
            }

        } elseif ($name === "master") {
            $masterConfigs = Server::$instance->getConfigContext()->get("yii.db.default.masters");
            if (empty($masterConfigs)) {
                $poolKey = "default";
                $contextKey = "Pdo:default";
            } else {
                $masterRandKey = array_rand($masterConfigs);

                $poolKey = sprintf("default.master.%s", $masterRandKey);
                $contextKey = sprintf("Pdo:default.master.%s", $masterRandKey);
            }
        }

        $db = getContextValue($contextKey);

        if ($db == null) {
            /** @var PdoPools $pdoPools */
            $pdoPools = getDeepContextValueByClassName(PdoPools::class);
            if (!empty($pdoPools)) {
                $pool = $pdoPools->getPool($poolKey);
                if ($pool == null) {
                    throw new \PDOException("No Pdo connection pool named {$poolKey} was found");
                }
                return $pool->db();
            } else {
                return $this->getDbOnce();
            }

        } else {
            return $db;
        }
    }

    /**
     * Get db once
     * @return Connection
     * @throws \ESD\Yii\Db\Exception
     */
    public function getDbOnce()
    {
        $config = Server::$instance->getConfigContext()->get("yii.db.default");
        $db = new Connection();
        $db->dsn = $config['dsn'];
        $db->username = $config['username'];
        $db->password = $config['password'];
        $db->charset = $config['charset'] ?? 'utf8';
        $db->tablePrefix = $config['tablePrefix'];
        $db->enableSchemaCache = $config['enableSchemaCache'];
        $db->schemaCacheDuration = $config['schemaCacheDuration'];
        $db->schemaCache = $config['schemaCache'];
        $db->open();
        return $db;
    }

    /**
     * Returns the log dispatcher component.
     * @return \ESD\Yii\Log\Dispatcher the log dispatcher application component.
     * @throws InvalidConfigException
     */
    public function getLog()
    {
        return $this->get('log');
    }

    /**
     * Returns the request component.
     * @return \ESD\Core\Server\Beans\Request the request component.
     */
    public function getRequest()
    {
        $request = getDeepContextValueByClassName(Request::class);
        return $request;
    }

    /**
     * Returns the response component.
     * @return \ESD\Core\Server\Beans\Response the response component.
     */
    public function getResponse()
    {
        $response = getDeepContextValueByClassName(Response::class);
        return $response;
    }

    /**
     * Returns the formatter component.
     * @return \ESD\Yii\I18n\Formatter the formatter application component.
     */
    public function getFormatter()
    {
        return $this->get('formatter');
    }

    /**
     * Returns the internationalization (i18n) component
     * @return \ESD\Yii\I18n\I18N the internationalization application component.
     * @throws InvalidConfigException
     */
    public function getI18n()
    {
        return $this->get('i18n');
    }

    /**
     * Returns the cache component.
     * @return \ESD\Yii\Caching\Cache the cache application component. Null if the component is not enabled.
     * @throws InvalidConfigException
     */
    public function getCache()
    {
        return $this->get('cache');
    }

    /**
     * Returns the URL manager for this application.
     * @return \ESD\Yii\Web\UrlManager the URL manager for this application.
     */
    public function getUrlManager()
    {
        return $this->get('urlManager');
    }


    /**
     * Returns the asset manager.
     * @return \ESD\Yii\Web\AssetManager the asset manager application component.
     */
    public function getAssetManager()
    {
        return $this->get('assetManager');
    }

    /**
     * Returns the security component.
     * @return Security the security application component.
     * @throws InvalidConfigException
     */
    public function getSecurity()
    {
        return $this->get('security');
    }

    /**
     * Returns the view object.
     * @return View|\ESD\Yii\Web\View the view application component that is used to render various view files.
     */
    public function getView()
    {
        return $this->get('view');
    }

    /**
     * Returns the session component.
     * @return HttpSession the session component.
     */
    public function getSession()
    {
        $session = getDeepContextValueByClassName(HttpSession::class);
        if ($session == null) {
            $session = new HttpSession();
            if (!$session->isAvailable()) {
                $session->create();
            }
        }
        return $session;
    }

    /**
     * Returns the dynamic language
     *
     * @return string
     */
    public function getLanguage()
    {
        /** @var Request $request */
        $request = getDeepContextValueByClassName(Request::class);

        $inputLanguage = $cookieLanguage = '';
        if (!empty($request)) {
            /** @var string $inputLanguage */
            $inputLanguage = $request->input('language');
            /** @var string $cookieLanguage */
            $cookieLanguage = $request->cookie('language');
        }

        if (!empty($inputLanguage)) {
            $lang = $inputLanguage;
        } else if (!empty($cookieLanguage)) {
            $lang = $cookieLanguage;
        } else {
            $lang = $this->language;
        }

        return $lang;
    }

    /**
     * @return Connection|mixed
     * @throws \ESD\Yii\Db\Exception
     */
    public function getMongodb()
    {
        $poolKey = "default";
        $contextKey = "Mongodb:default";

        $db = getContextValue($contextKey);

        if ($db == null) {
            /** @var MongodbPools $pdoPools */
            $pdoPools = getDeepContextValueByClassName(MongodbPools::class);
            if (!empty($pdoPools)) {
                $pool = $pdoPools->getPool($poolKey);
                if ($pool == null) {
                    throw new \PDOException("No Pdo connection pool named {$poolKey} was found");
                }
                return $pool->db();
            } else {
                return $this->getDbOnce();
            }

        } else {
            return $db;
        }
    }

    /**
     * Get db once
     * @return Connection
     * @throws \ESD\Yii\Mongodb\Exception
     */
    public function getMongodbOnce()
    {
        $config = Server::$instance->getConfigContext()->get("yii.db.mongodb");
        $db = new \ESD\Yii\Mongodb\Connection();
        $db->dsn = $config['dsn'];
        $db->username = $config['username'];
        $db->password = $config['password'];
        $db->options = $config['options'] ?? [];
        $db->tablePrefix = $config['tablePrefix'];
        $db->enableSchemaCache = $config['enableSchemaCache'];
        $db->schemaCacheDuration = $config['schemaCacheDuration'];
        $db->schemaCache = $config['schemaCache'];
        $db->open();
        return $db;
    }

    /**
     * Returns the configuration of core application components.
     * @see set()
     */
    public function coreComponents()
    {
        return [
            'formatter' => ['class' => '\ESD\Yii\I18n\Formatter'],
            'i18n' => ['class' => 'ESD\Yii\I18n\I18N'],
            'log' => ['class' => 'ESD\Yii\Log\Dispatcher'],
            'security' => ['class' => 'ESD\Yii\Base\Security'],
            'view' => ['class' => 'ESD\Yii\Web\View'],
            'urlManager' => ['class' => 'ESD\Yii\Web\UrlManager'],
            'assetManager' => ['class' => 'ESD\Yii\Web\AssetManager'],
            'security' => ['class' => 'ESD\Yii\Base\Security'],
            'view' => ['class' => 'ESD\Yii\Web\View'],
            'urlManager' => ['class' => 'ESD\Yii\Web\UrlManager']
        ];
    }
}