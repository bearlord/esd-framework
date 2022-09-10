<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\SaberCloud;


use ESD\Core\Plugins\Config\BaseConfig;

class SaberCloudConfig extends BaseConfig
{
    const key = "saber_cloud";
    protected $retryTime = 3;

    public function __construct()
    {
        parent::__construct(self::key);
    }

    /**
     * @return int
     */
    public function getRetryTime(): int
    {
        return $this->retryTime;
    }

    /**
     * @param int $retryTime
     */
    public function setRetryTime(int $retryTime): void
    {
        $this->retryTime = $retryTime;
    }
}