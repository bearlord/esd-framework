<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ESD\Yii\Mongodb\Rbac;

/**
 * Permission is a special version of [[\yii\rbac\Permission]] dedicated to MongoDB RBAC implementation.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0.5
 */
class Permission extends \ESD\Yii\Rbac\Permission
{
    /**
     * @var array|null list of parent item names.
     */
    public $parents;
}