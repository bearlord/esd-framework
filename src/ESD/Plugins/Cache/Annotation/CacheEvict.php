<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/8
 * Time: 11:08
 */

namespace ESD\Plugins\Cache\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class CacheEvict extends Annotation
{
    /**
     * 代表需要删除的命名空间下唯一的缓存key。
     * 使用php语法，$p[0]获取对应参数
     * @var string
     */
    public $key = "";

    /**
     * 命名空间
     * @var string
     */
    public $namespace = "";

    /**
     * 标记是否删除命名空间下所有缓存，默认为false
     * @var bool
     */
    public $allEntries = false;

    /**
     *  清除操作默认是在对应方法成功执行之后触发的，即方法如果因为抛出异常而未能成功返回时也不会触发清除操作。
     *  使用beforeInvocation可以改变触发清除操作的时间，当我们指定该属性值为true时，会在调用该方法之前清除缓存中的指定元素。
     * @var bool
     */
    public $beforeInvocation = false;
}
