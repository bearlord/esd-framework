<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Pack;

/**
 * Trait GetClientData
 * @package ESD\Plugins\Pack
 */
trait GetClientData
{
    /**
     * @return ClientData|null
     */
    public function getClientData(): ?ClientData
    {
       return getDeepContextValueByClassName(ClientData::class);
    }
}