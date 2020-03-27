<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/8
 * Time: 15:51
 */

namespace ESD\Plugins\Security\Annotation;


use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class PreAuthorize extends Annotation
{
    /**
     * @var string
     */
    public $value;
}