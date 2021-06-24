<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Aop;

use Doctrine\Common\Cache\ArrayCache;
use ESD\Core\Context\Context;
use ESD\Core\Exception;
use ESD\Core\Order\OrderOwnerTrait;
use ESD\Core\Plugin\AbstractPlugin;
use ESD\Core\Plugins\Config\ConfigException;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Core\Server\Server;
use ESD\Yii\Yii;
use ESD\Aop\Aop;
use ESD\Aop\AopAspectKernel;
use ESD\Aop\GoAspectContainer;

/**
 * Class AopPlugin
 * @package ESD\Plugins\Aop
 */
class AopPlugin extends AbstractPlugin
{
    use OrderOwnerTrait;
    use GetLogger;

    /**
     * @var AopConfig
     */
    private $aopConfig;

    /**
     * @var array
     */
    private $options;

    /** @var ApplicationAspectKernel */
    protected $applicationAspectKernel;

    /**
     * AopPlugin constructor.
     * @param AopConfig|null $aopConfig
     * @throws \ReflectionException
     */
    public function __construct(?AopConfig $aopConfig = null)
    {
        parent::__construct();
        if ($aopConfig == null) {
            $aopConfig = new AopConfig();
        }
        $this->aopConfig = $aopConfig;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName(): string
    {
        return "Aop";
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @throws ConfigException
     * @throws Exception
     * @throws \Exception
     */
    public function init(Context $context)
    {
        parent::init($context);
        //File operations must close the global RuntimeCoroutine
        enableRuntimeCoroutine(false);
        $cacheDir = $this->aopConfig->getCacheDir() ?? Server::$instance->getServerConfig()->getBinDir() . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . "aop";
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        $this->aopConfig->merge();

        //Add src directory automatically
        $serverConfig = Server::$instance->getServerConfig();
        $this->aopConfig->addIncludePath($serverConfig->getSrcDir());
        $this->aopConfig->addIncludePath($serverConfig->getVendorDir() . "/bearlord/esd-framework/src/ESD");
        $this->aopConfig->setCacheDir($cacheDir);

        $serverConfig = Server::$instance->getServerConfig();
        //Exclude paths
        $excludePaths = Server::$instance->getConfigContext()->get("esd.aop.excludePaths");
        if (!empty($excludePaths)) {
            foreach ($excludePaths as $excludePath) {
                $this->aopConfig->addExcludePath($excludePath);
            }
        }

        $this->aopConfig->merge();

        $this->applicationAspectKernel = ApplicationAspectKernel::getInstance();
        $this->applicationAspectKernel->setConfig($this->aopConfig);
        $this->options = [
            //Use 'false' for production mode
            'debug' => $serverConfig->isDebug(),
            //Application root directory
            'appDir' => $serverConfig->getRootDir(),
            //Cache directory
            'cacheDir' => $this->aopConfig->getCacheDir(),
            //Include paths
            'includePaths' => $this->aopConfig->getIncludePaths(),
            //Exclude paths
            'excludePaths' => $this->aopConfig->getExcludePaths()
        ];
        if (!$this->aopConfig->isFileCache()) {
            $this->options['annotationCache'] = new ArrayCache();
        }
        $this->applicationAspectKernel->initContainer($this->options);
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @throws Exception
     */
    public function beforeServerStart(Context $context)
    {
        $serverConfig = Server::$instance->getServerConfig();
        $this->options = [
            //Use 'false' for production mode
            'debug' => $serverConfig->isDebug(),
            //Application root directory
            'appDir' => $serverConfig->getRootDir(),
            //Cache directory
            'cacheDir' => $this->aopConfig->getCacheDir(),
            //Include paths
            'includePaths' => $this->aopConfig->getIncludePaths(),
            //Exclude paths
            'excludePaths' => $this->aopConfig->getExcludePaths()
        ];

        $this->applicationAspectKernel->init($this->options);

    }

    /**
     * @inheritDoc
     * @param Context $context
     */
    public function beforeProcessStart(Context $context)
    {
        $this->ready();
    }

    /**
     * @return AopConfig
     */
    public function getAopConfig(): AopConfig
    {
        return $this->aopConfig;
    }

    /**
     * @param AopConfig $aopConfig
     */
    public function setAopConfig(AopConfig $aopConfig): void
    {
        $this->aopConfig = $aopConfig;
    }

}