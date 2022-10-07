<?php

namespace ESD\Plugins\Cloud\Registry;

use ESD\Core\Plugin\AbstractPlugin;

class RegistryPlugin extends AbstractPlugin
{
    /**
     * @inheritDoc
     * @return string
     */
    public function getName(): string
    {
        return "Registry";
    }
}