<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/21
 * Time: 17:05
 */

namespace ESD\Plugins\Uid;


use ESD\Core\Plugins\Config\BaseConfig;

class UidConfig extends BaseConfig
{
    const key = "uid";
    protected $uidMaxLength = 24;

    public function __construct()
    {
        parent::__construct(self::key);
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