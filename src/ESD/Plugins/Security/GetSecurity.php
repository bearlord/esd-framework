<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Security;

use ESD\Plugins\Security\Beans\Principal;
use ESD\Plugins\Session\GetSession;

trait GetSecurity
{
    use GetSession;

    public function getPrincipal(): ?Principal
    {
        return $this->getSession()->getAttribute("Principal");
    }

    public function setPrincipal(Principal $principal)
    {
        $this->getSession()->setAttribute("Principal", $principal);
    }
}