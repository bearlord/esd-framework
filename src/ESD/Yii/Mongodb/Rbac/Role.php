<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ESD\Yii\Mongodb\Rbac;

/**
 * Role is a special version of [[\ESD\Yii\Rbac\Role]] dedicated to MongoDB RBAC implementation.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0.5
 */
class Role extends \ESD\Yii\Rbac\Role
{
    /**
     * @var array|null list of parent item names.
     */
    public $parents;
}