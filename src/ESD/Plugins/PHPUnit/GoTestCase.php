<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\PHPUnit;

use ESD\Server\Coroutine\Server;
use PHPUnit\Framework\TestCase;

/**
 * Class GoTestCase
 * @package ESD\Plugins\PHPUnit
 */
class GoTestCase extends TestCase
{
    /**
     * GoTestCase constructor.
     * @param null $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        Server::$instance->getContainer()->injectOn($this);
    }
}