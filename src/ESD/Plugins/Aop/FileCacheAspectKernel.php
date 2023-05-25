<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Aop;

use ESD\Goaop\Core\AspectContainer;
use ESD\Goaop\Core\AspectKernel;

/**
 * Class FileCacheAspectKernel
 * @package ESD\Plugins\Aop
 */
class FileCacheAspectKernel extends AspectKernel
{
    /**
     * @var array
     */
    protected $aspects = [];

    /**
     * @param array $aspects
     * @return FileCacheAspectKernel
     */
    public function setAspects(array $aspects): self
    {
        $this->aspects = $aspects;
        return $this;
    }

    /**
     * @param AspectContainer $container
     */
    protected function configureAop(AspectContainer $container)
    {
        foreach ($this->aspects as $aspect) {
            $container->registerAspect($aspect);
        }
    }
}