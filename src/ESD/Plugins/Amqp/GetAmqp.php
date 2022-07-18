<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Amqp;
use ESD\Core\Server\Server;
use ESD\Yii\Yii;
use PhpAmqpLib\Connection\AbstractConnection;

/**
 * Trait GetAmqp
 * @package ESD\Plugins\Amqp
 */
trait GetAmqp
{
    /**
     * @param string $name
     * @return mixed|AbstractConnection
     * @throws \Exception
     */
    public function amqp(string $name = 'default')
    {
        $poolKey = $name;
        $contextKey = sprintf("Amqp:%s", $name);

        $db = getContextValue($contextKey);
        if ($db == null) {
            /** @var AmqpPools $pdoPools * */
            $pdoPools = getDeepContextValueByClassName(AmqpPools::class);
            $pool = $pdoPools->getPool($poolKey);
            if ($pool == null) {
                throw new \Exception("No Amqp connection pool named {$poolKey} was found");
            }
            return $pool->db();
        } else {
            return $db;
        }
    }

    /**
     * @param string $name
     * @return AbstractConnection
     * @throws \Exception
     */
    public function amqpOnce(string $name = 'default')
    {
        $configs = Server::$instance->getConfigContext()->get('amqp');
        foreach ($configs as $key => $config) {
            $configObject = new Config($key);
            $configObject->setHosts($config['hosts']);
            try {
                $connection = new Connection($configObject);
                return $connection->getActiveConnection();
            } catch (AmqpException $exception) {
                throw $exception;
            }

        }
    }
}