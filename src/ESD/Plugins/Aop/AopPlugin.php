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
use ESD\Core\PlugIn\AbstractPlugin;
use ESD\Core\Plugins\Config\ConfigException;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Core\Server\Server;
use rabbit\aop\Aop;
use rabbit\aop\AopAspectKernel;
use rabbit\aop\GoAspectContainer;

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

        $serverConfig = Server::$instance->getServerConfig();

        //Add src directory automatically
        $this->aopConfig->addIncludePath($serverConfig->getSrcDir());

        $this->aopConfig->addIncludePath($serverConfig->getVendorDir() . "/bearlord");
        $this->aopConfig->setCacheDir($cacheDir);
        $this->aopConfig->merge();
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
            'debug' => $serverConfig->isDebug(), // use 'false' for production mode
            'appDir' => $serverConfig->getRootDir(), // Application root directory
            'cacheDir' => $this->aopConfig->getCacheDir(), // Cache directory
            'includePaths' => $this->aopConfig->getIncludePaths(),
            'excludePaths' => $this->aopConfig->getExcludePaths()
        ];
        foreach ($this->aopConfig->getAspects() as $aspect) {
            $this->addOrder($aspect);
        }
        $this->order();
        foreach ($this->orderList as $aspect) {
            $this->debug("Add aspect {$aspect->getName()}");
        }
        if (!$this->aopConfig->isFileCache()) {
            $this->options['annotationCache'] = new ArrayCache();
            $this->options['containerClass'] = new GoAspectContainer();
            new Aop(AopAspectKernel::class, $this->orderList, $this->options);
        } else {
            new Aop(FileCacheAspectKernel::class, $this->orderList, $this->options);
        }

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