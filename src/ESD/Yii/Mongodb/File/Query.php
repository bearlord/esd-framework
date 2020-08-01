<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ESD\Yii\Mongodb\File;

use ESD\Yii\Yii;

/**
 * Query represents Mongo "find" operation for GridFS collection.
 *
 * Query behaves exactly as regular [[\ESD\Yii\Mongodb\Query]].
 * Found files will be represented as arrays of file document attributes with
 * additional 'file' key, which stores [[\MongoGridFSFile]] instance.
 *
 * @property Collection $collection Collection instance. This property is read-only.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Query extends \ESD\Yii\Mongodb\Query
{
    /**
     * Returns the Mongo collection for this query.
     * @param \ESD\Yii\Mongodb\Connection $db Mongo connection.
     * @return Collection collection instance.
     */
    public function getCollection($db = null)
    {
        if ($db === null) {
            $db = Yii::$app->getMongodb();
        }

        return $db->getFileCollection($this->from);
    }
}
