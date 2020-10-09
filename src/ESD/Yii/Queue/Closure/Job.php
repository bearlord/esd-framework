<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ESD\Yii\Queue\Closure;

use SuperClosure\Serializer;
use ESD\Yii\Queue\JobInterface;

/**
 * Closure Job.
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class Job implements JobInterface
{
    /**
     * @var string serialized closure
     */
    public $serialized;


    /**
     * Unserializes and executes a closure.
     * @inheritdoc
     */
    public function execute($queue)
    {
        $serializer = new Serializer();
        $closure = $serializer->unserialize($this->serialized);
        return $closure();
    }
}
