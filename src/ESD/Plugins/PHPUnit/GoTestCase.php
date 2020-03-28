<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/9
 * Time: 15:33
 */

namespace ESD\Plugins\PHPUnit;


use ESD\Core\Server\Server;
use PHPUnit\Framework\TestCase;

class GoTestCase extends TestCase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        Server::$instance->getContainer()->injectOn($this);
    }
}