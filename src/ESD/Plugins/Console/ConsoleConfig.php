<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Console;

use ESD\Core\Plugins\Config\BaseConfig;

/**
 * Class ConsoleConfig
 * @package ESD\Plugins\Console
 */
class ConsoleConfig extends BaseConfig
{
    const key = "console";
    /**
     * @var string[]
     */
    protected $cmdClassList = [];

    /**
     * ConsoleConfig constructor.
     */
    public function __construct()
    {
        parent::__construct(self::key);
    }

    /**
     * @return string[]
     */
    public function getCmdClassList(): array
    {
        return $this->cmdClassList;
    }

    /**
     * @param string[] $cmdClassList
     */
    public function setCmdClassList(array $cmdClassList): void
    {
        $this->cmdClassList = $cmdClassList;
    }

    /**
     * @param string $className
     */
    public function addCmdClass(string $className): void
    {
        $list = explode("\\", $className);
        $this->cmdClassList[$list[count($list) - 1]] = $className;
    }

}