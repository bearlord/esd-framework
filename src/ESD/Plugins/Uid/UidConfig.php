<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Uid;

use ESD\Core\Plugins\Config\BaseConfig;

/**
 * Class UidConfig
 * @package ESD\Plugins\Uid
 */
class UidConfig extends BaseConfig
{
    const KEY = "uid";
    
    protected $uidMaxLength = 24;

    /**
     * UidConfig constructor.
     */
    public function __construct()
    {
        parent::__construct(self::KEY);
    }

    /**
     * @return int
     */
    public function getUidMaxLength(): int
    {
        return $this->uidMaxLength;
    }

    /**
     * @param int $uidMaxLength
     */
    public function setUidMaxLength(int $uidMaxLength): void
    {
        $this->uidMaxLength = $uidMaxLength;
    }
}