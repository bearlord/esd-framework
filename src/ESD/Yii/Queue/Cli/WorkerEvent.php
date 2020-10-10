<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ESD\Yii\Queue\Cli;

use ESD\Yii\Base\Event;

/**
 * Worker Event.
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 * @since 2.0.2
 */
class WorkerEvent extends Event
{
    /**
     * @var Queue
     * @inheritdoc
     */
    public $sender;

    /**
     * @var null|int exit code
     */
    public $exitCode;
}
