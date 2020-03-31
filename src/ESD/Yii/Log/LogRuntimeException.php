<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ESD\Yii\Log;


/**
 * LogRuntimeException represents an exception caused by problems with log delivery.
 *
 * @author Bizley <pawel@positive.codes>
 * @since 2.0.14
 */
class LogRuntimeException extends \ESD\Yii\Base\Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Log Runtime';
    }
}
