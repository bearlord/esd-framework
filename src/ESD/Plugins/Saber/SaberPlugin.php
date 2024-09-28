<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Saber;

use ESD\Core\Context\Context;
use ESD\Core\Plugin\AbstractPlugin;
use Swlib\SaberGM;

/**
 * Class SaberPlugin
 * @package ESD\Plugins\Saber
 */
class SaberPlugin extends AbstractPlugin
{
    /**
     * @var SaberConfig
     */
    private $saberConfig;

    /**
     * SaberPlugin constructor.
     * @param \ESD\Plugins\Saber\SaberConfig|null $saberConfig
     */
    public function __construct(?SaberConfig $saberConfig = null)
    {
        parent::__construct();
        $this->saberConfig = $saberConfig;
        if ($this->saberConfig == null) {
            $this->saberConfig = new SaberConfig();
        }
    }

    /**
     * @inheritDoc
     * @return string
     */
    public function getName(): string
    {
        return "Saber";
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @throws \ESD\Core\Plugins\Config\ConfigException
     * @throws \ReflectionException
     */
    public function beforeServerStart(Context $context)
    {
        $this->saberConfig->merge();
        SaberGM::default($this->saberConfig->buildConfig());
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
     * @return SaberConfig
     */
    public function getSaberConfig(): SaberConfig
    {
        return $this->saberConfig;
    }

    /**
     * @param SaberConfig $saberConfig
     */
    public function setSaberConfig(SaberConfig $saberConfig): void
    {
        $this->saberConfig = $saberConfig;
    }
}
