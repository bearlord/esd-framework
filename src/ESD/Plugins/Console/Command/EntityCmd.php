<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Console\Command;

use ESD\Plugins\Console\Model\Logic\SchemaLogic;
use ESD\Core\Context\Context;
use ESD\Plugins\Console\ConsolePlugin;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class EntityCmd
 * @package ESD\Plugins\Console\Command
 */
class EntityCmd extends Command
{

    /**
     * @var Context
     */
    private $context;

    /**
     * StartCmd constructor.
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct();
        $this->context = $context;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('entity')->setDescription("Entity generator");
        $this->addArgument('pool', InputArgument::OPTIONAL, 'database db pool?', 'default');
        $this->addOption('table', 't', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'database table name?', []);
        $this->addOption('path', null, InputOption::VALUE_OPTIONAL, 'generate entity file path?', '@app/Model/Entity');
        $this->addOption('template', null, InputOption::VALUE_OPTIONAL, 'generate entity template path?', '@devtool/resources');
        $this->addOption('extend', null, InputOption::VALUE_OPTIONAL, 'generate extend class?', '\\ESD\\Plugins\\Console\\Model\\GoModel');
        $this->addOption('confirm', 'y', InputOption::VALUE_NONE, 'confirm execution?');
    }

    /**
     * @inheritDoc
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws \ESD\Core\Exception
     * @throws \ESD\Plugins\Mysql\MysqlException
     * @throws \ReflectionException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $pool = $input->getArgument('pool');

        $tables = $input->getOption('table');
        $path = $input->getOption('path');
        $tpl = $input->getOption('template');
        $extendClass = $input->getOption('extend');
        $confirm = $input->getOption('confirm');

        $schemaLogic = new SchemaLogic($pool, $io);
        $schemaLogic->create($path, $tpl, $tables, $extendClass, $confirm);
        return ConsolePlugin::SUCCESS_EXIT;
    }
}