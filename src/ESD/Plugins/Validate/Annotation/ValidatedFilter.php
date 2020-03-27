<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/21
 * Time: 9:28
 */

namespace ESD\Plugins\Validate\Annotation;


use ESD\Plugins\Validate\ValidationException;
use ReflectionClass;

class ValidatedFilter
{
    /**
     * @param ReflectionClass|string $reflectionClass
     * @param $values
     * @param array $roles
     * @param array $messages
     * @param array $translates
     * @param string $scene
     * @return array|\stdClass
     * @throws ValidationException
     * @throws \ReflectionException
     */
    public static function valid($reflectionClass, $values, $roles = [], $messages = [], $translates = [], $scene = "")
    {
        $result = Filter::filter($reflectionClass, $values);
        $result = Validated::valid($reflectionClass, $result, $roles, $messages, $translates, $scene);
        return $result;
    }
}