<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Console\Command;

use ESD\Core\Context\Context;
use ESD\Plugins\Console\ConsolePlugin;
use ESD\Core\Server\Server;
use ESD\Yii\Console\Application;
use ESD\Yii\Yii;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class YiiCmd extends Command
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var Application 
     */
    private $app;

    /**
     * StartCmd constructor.
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct();
        $this->context = $context;
        $this->app = Application::instance();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('yii')->setDescription("Yii console");
        $this->addArgument('route', InputOption::VALUE_NONE, 'Route');
    }

    /**
     * @inheritDoc
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $serverConfig = Server::$instance->getServerConfig();

        $arguments = $input->getArguments();

        $route = $input->getArgument('route');
        unset($arguments['command'], $arguments['route']);

        var_dump($route, $arguments);
        $content = Application::instance()->runAction($route, $arguments);

        $io->success(sprintf("Route %s execute success", $route));
        return ConsolePlugin::SUCCESS_EXIT;
    }
}