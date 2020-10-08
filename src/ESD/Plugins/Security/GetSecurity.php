<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Security;

use ESD\Plugins\Security\Beans\Principal;
use ESD\Plugins\Session\GetSession;

/**
 * Trait GetSecurity
 * @package ESD\Plugins\Security
 */
trait GetSecurity
{
    use GetSession;

    /**
     * @return Principal|null
     */
    public function getPrincipal(): ?Principal
    {
        return $this->getSession()->getAttribute("Principal");
    }

    /**
     * @param Principal $principal
     */
    public function setPrincipal(Principal $principal)
    {
        $this->getSession()->setAttribute("Principal", $principal);
    }
}