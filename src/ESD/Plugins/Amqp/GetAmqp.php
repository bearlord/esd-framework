<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Amqp;
use AMQPConnection;
use ESD\Core\Server\Server;
use ESD\Yii\Yii;

/**
 * Trait GetAmqp
 * @package ESD\Plugins\Amqp
 */
trait GetAmqp
{
    /**
     * @param string $name
     * @return AMQPConnection|\ESD\Plugins\Amqp\AmqpConnection|null
     * @throws AmqpException
     */
    public function amqp(string $name = 'default')
    {
        $poolKey = sprintf("Amqp:%s", $name);

        /** @var AMQPConnection $connection */
        $connection = getContextValue($poolKey);
        if ($connection == null || !$connection->isConnected()) {
            $amqpPool = getDeepContextValueByClassName(AmqpPool::class);
            if ($amqpPool instanceof AmqpPool) {
                $connection = $amqpPool->getConnection($name);
                setContextValue($poolKey, $connection);
                return $connection;
            } else {
                throw new AmqpException("No Amqp connection pool named {$poolKey} was found");
            }
        } else {
            return $connection;
        }
    }

    /**
     * @param string $name
     * @return AMQPConnection
     * @throws AmqpException
     */
    public function amqpOnce(string $name = 'default')
    {
        $configs = Server::$instance->getConfigContext()->get('amqp');
        foreach ($configs as $key => $config) {
            $configObject = new Config($key);
            $configObject->setHosts($config['hosts']);
            try {
                $connection = new Connection($configObject);
                return $connection->getConnection();
            } catch (AmqpException $exception) {
                throw $exception;
            }

        }
    }
}