<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Actor\Log;

use ESD\Yii\Yii;

class LogFactory
{
    /**
     * @param string $name
     * @return Logger
     */
    public static function create(string $name): Logger
    {
        return Yii::createObject([
            "class" => Logger::class,
            "flushInterval" => 1,
            "dispatcher" => Yii::createObject([
                "class" => Dispatcher::class,
                "targets" => [
                    Yii::createObject([
                        "class" => FileTarget::class,
                        "logFileName" => $name,
                        "exportInterval" => 2,
                    ])
                ]
            ])
        ]);
    }
}