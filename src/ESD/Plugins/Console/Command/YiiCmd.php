<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Console\Command;

use ESD\Core\Context\Context;
use ESD\Plugins\Console\ConsolePlugin;
use ESD\Server\Coroutine\Server;
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

        $argMaxNumber = 50;
        for ($i = 1; $i <= $argMaxNumber; $i++) {
            $this->addArgument('arg' . $i, 2, 'arg' . $i);
        }
    }

    /**
     * @inheritDoc
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $arguments = $input->getArguments();
        unset($arguments['command'], $arguments['route']);

        $prettyArguments = array_values($arguments);
        $route = $input->getArgument('route');
        $content = Application::instance()->runAction($route, $prettyArguments);

        $io = new SymfonyStyle($input, $output);
        $io->text($content);

        $io->success(sprintf("Route %s execute success", $route));
        return ConsolePlugin::SUCCESS_EXIT;
    }
}