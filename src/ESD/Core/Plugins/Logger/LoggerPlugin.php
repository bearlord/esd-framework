<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Plugins\Logger;

use ESD\Core\Context\Context;
use ESD\Core\PlugIn\AbstractPlugin;
use ESD\Core\Plugins\Config\ConfigChangeEvent;
use ESD\Core\Plugins\Config\ConfigPlugin;
use ESD\Core\Plugins\Event\EventDispatcher;
use ESD\Core\Server\Server;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;

/**
 * Class LoggerPlugin
 * @package ESD\Core\Plugins\Logger
 */
class LoggerPlugin extends AbstractPlugin
{
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var LoggerConfig
     */
    private $loggerConfig;
    /**
     * @var GoSwooleProcessor
     */
    private $goSwooleProcessor;

    /**
     * LoggerPlugin constructor.
     * @param LoggerConfig|null $loggerConfig
     */
    public function __construct(?LoggerConfig $loggerConfig = null)
    {
        parent::__construct();
        $this->atAfter(ConfigPlugin::class);
        if ($loggerConfig == null) {
            $loggerConfig = new LoggerConfig();

        }
        $this->loggerConfig = $loggerConfig;
    }

    /**
     * @param Context $context
     * @throws \Exception
     */
    private function buildLogger(Context $context)
    {
        $this->logger = new Logger($this->loggerConfig->getName());
        $formatter = new LineFormatter($this->loggerConfig->getOutput(),
            $this->loggerConfig->getDateFormat(),
            $this->loggerConfig->isAllowInlineLineBreaks(),
            $this->loggerConfig->isIgnoreEmptyContextAndExtra());

        //Screen print
        $handler = new StreamHandler('php://stderr', $this->loggerConfig->getLevel());
        $handler->setFormatter($formatter);

        $this->logger->pushHandler($handler);

        $this->goSwooleProcessor = new GoSwooleProcessor($this->loggerConfig->isColor());
        $this->logger->pushProcessor($this->goSwooleProcessor);
        $this->logger->pushProcessor(new GoIntrospectionProcessor());

        DISet(LoggerInterface::class, $this->logger);
        DISet(\Monolog\Logger::class, $this->logger);
        DISet(Logger::class, $this->logger);
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @throws \ESD\Core\Exception
     * @throws \Exception
     */
    public function beforeServerStart(Context $context)
    {
        $this->loggerConfig->merge();
        $this->buildLogger($context);
        foreach ($this->logger->getHandlers() as $handler) {
            if ($handler instanceof AbstractHandler) {
                $handler->setLevel($this->loggerConfig->getLevel());
            }
        }
        $this->goSwooleProcessor->setColor($this->loggerConfig->isColor());
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @throws \ESD\Core\Exception
     */
    public function beforeProcessStart(Context $context)
    {
        $serverConfig = Server::$instance->getServerConfig();
        if (Server::$instance->getServerConfig()->isDaemonize()) {
            //Remove screen print handler
            $this->logger->popHandler();

            //Add a log handler
            $handler = new RotatingFileHandler($serverConfig->getBinDir() . "/logs/" . $this->loggerConfig->getName() . ".log",
                $this->loggerConfig->getMaxFiles(),
                $this->loggerConfig->getLevel());
            $this->logger->pushHandler($handler);
            $this->goSwooleProcessor->setColor(false);
        }

        //Monitoring configuration updates
        goWithContext(function () use ($context) {
            $eventDispatcher = DIGet(EventDispatcher::class);
            $call = $eventDispatcher->listen(ConfigChangeEvent::ConfigChangeEvent);
            $call->call(function ($result) {
                $this->loggerConfig->merge();
                foreach ($this->logger->getHandlers() as $handler) {
                    if ($handler instanceof AbstractHandler) {
                        $handler->setLevel($this->loggerConfig->getLevel());
                    }
                }
            });
        });
        $this->ready();
    }

    /**
     * @inheritDoc
     * @return string
     */
    public function getName(): string
    {
        return "Logger";
    }

    /**
     * Get logger
     * @return mixed
     */
    public function getLogger()
    {
        return $this->logger;
    }
}