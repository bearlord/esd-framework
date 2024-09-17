<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ESD\Yii\Elasticsearch;

/**
 * Exception represents an exception that is caused by Elasticsearch-related operations.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class Exception extends \ESD\Yii\Db\Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName(): string
    {
        return 'Elasticsearch Database Exception';
    }
}
