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
 * 对于使用@Cacheable标注的方法，在每次执行前都会检查Cache中是否存在相同key的缓存元素，
 * 如果存在就不再执行该方法，而是直接从缓存中获取结果进行返回，否则才会执行并将返回结果存入指定的缓存中。
 * @CachePut也可以声明一个方法支持缓存功能。
 * 与@Cacheable不同的是使用@CachePut标注的方法在执行前不会去检查缓存中是否存在之前执行过的结果，
 * 而是每次都会执行该方法，并将执行结果以键值对的形式存入指定的缓存中。
 * @Annotation
 * @Target("METHOD")
 */
class CachePut extends Annotation
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
