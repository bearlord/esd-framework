<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Rpc\Client;

use ESD\Yii\Base\Component;

/**
 * Class Client
 * @package ESD\Rpc\Client
 */
abstract class Client
{
    abstract public function send($data);
}