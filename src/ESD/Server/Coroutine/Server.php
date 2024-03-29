<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Server\Coroutine;

use ESD\Core\Channel\Channel;
use ESD\Core\DI\DI;
use ESD\Core\Plugins\Event\EventCall;
use ESD\Core\Server\Beans\AbstractRequest;
use ESD\Core\Server\Beans\AbstractResponse;
use ESD\Core\Server\Config\ServerConfig;
use ESD\Coroutine\Channel\ChannelFactory;
use ESD\Coroutine\Coroutine;
use ESD\Coroutine\Event\EventCallFactory;
use ESD\Server\Coroutine\Http\Factory\RequestFactory;
use ESD\Server\Coroutine\Http\Factory\ResponseFactory;
use ESD\Yii\Yii;

/**
 * Class Server
 * @package ESD\Server\Coroutine
 */
abstract class Server extends \ESD\Core\Server\Server
{
    /**
     * Server constructor.
     * @param ServerConfig|null $serverConfig
     * @param string $defaultPortClass
     * @param string $defaultProcessClass
     * @throws \ESD\Core\Exception
     */
    public function __construct(?ServerConfig $serverConfig, string $defaultPortClass, string $defaultProcessClass)
    {
        Coroutine::enableCoroutine();
        DI::$definitions = [
            Channel::class => new ChannelFactory(),
            EventCall::class => new EventCallFactory(),
            AbstractRequest::class => new RequestFactory(),
            AbstractResponse::class => new ResponseFactory(),
        ];

        if ($serverConfig == null) {
            $serverConfig = new ServerConfig();
        }

        if ($serverConfig->isDebug()) {
            error_reporting(E_ALL &  ~E_WARNING);
            ini_set("display_errors", "On");
        }

        parent::__construct($serverConfig, $defaultPortClass, $defaultProcessClass);
    }

    /**
     * @throws \ESD\Core\Exception
     * @throws \ESD\Core\Plugins\Config\ConfigException
     * @throws \ReflectionException
     */
    public function configure()
    {
        parent::configure();
    
        $this->getLog()->debug(Yii::t("esd", "Print configuration") . ":\n" . $this->getConfigContext()->getCacheContainYaml());
    }
}