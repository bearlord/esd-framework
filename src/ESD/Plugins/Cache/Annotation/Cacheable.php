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
class Cacheable extends Annotation
{
    /**
     * 缓存时间0代表使用默认时间，-1代表无限时间,对有namespace的无效
     *
     * @var int
     */
    public $time = 0;

    /**
     * 代表需要删除的命名空间下唯一的缓存key。
     * 使用php语法，$p[0]获取对应参数
     * @var string
     */
    public $key = "";

    /**
     * 有的时候我们可能并不希望缓存一个方法所有的返回结果。
     * 通过condition属性可以实现这一功能。condition属性默认为空，表示将缓存所有的调用情形。
     * 其值是通过PHP表达式来指定的，当为true时表示进行缓存处理；
     * 当为false时表示不进行缓存处理，即每次调用该方法时该方法都会执行一次。
     * @var string
     */
    public $condition = "";

    /**
     * 命名空间
     * @var string
     */
    public $namespace = "";
}
