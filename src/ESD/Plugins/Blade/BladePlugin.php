<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Blade;

use ESD\Core\Context\Context;
use ESD\Core\PlugIn\AbstractPlugin;
use ESD\Core\Server\Server;

/**
 * Class BladePlugin
 * @package ESD\Plugins\Blade
 */
class BladePlugin extends AbstractPlugin
{
    /**
     * @var Blade
     */
    protected $blade;
    /**
     * @var BladeConfig|null
     */
    private $bladeConfig;

    /**
     * BladePlugin constructor.
     *
     * @param BladeConfig|null $bladeConfig
     * @throws \ReflectionException
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function __construct(?BladeConfig $bladeConfig = null)
    {
        parent::__construct();
        if ($bladeConfig == null) $bladeConfig = new BladeConfig();
        $this->bladeConfig = $bladeConfig;
    }

    /**
     * @inheritDoc
     * @return string
     */
    public function getName(): string
    {
        return "Blade";
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \ESD\Core\Plugins\Config\ConfigException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \ESD\Core\Exception
     * @throws \Exception
     */
    public function beforeServerStart(Context $context)
    {
        $this->bladeConfig->merge();
        if (empty($this->bladeConfig->getCachePath())) {
            $cacheDir = Server::$instance->getServerConfig()->getCacheDir() . "/blade";
            $this->bladeConfig->setCachePath($cacheDir);
        }
        $this->bladeConfig->merge();
        $cacheDir = $this->bladeConfig->getCachePath();
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        $this->blade = new Blade($this->bladeConfig->getCachePath());
        foreach ($this->bladeConfig->getNamespace() as $value) {
            $this->blade->addNamespace($value->getName(), $value->getPath());
        }
        $this->setToDIContainer(Blade::class, $this->blade);
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @return mixed
     */
    public function beforeProcessStart(Context $context)
    {
        $this->ready();
    }
}