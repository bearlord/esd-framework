<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ESD\Yii\Queue\Beanstalk;

use ESD\Yii\Helpers\Console;
use ESD\Yii\Queue\Cli\Action;

/**
 * Info about queue status.
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class InfoAction extends Action
{
    /**
     * @var Queue
     */
    public $queue;


    /**
     * Info about queue status.
     */
    public function run()
    {
        Console::output($this->format('Statistical information about the tube:', Console::FG_GREEN));

        foreach ($this->queue->getStatsTube() as $key => $value) {
            Console::stdout($this->format("- $key: ", Console::FG_YELLOW));
            Console::output($value);
        }
    }
}
