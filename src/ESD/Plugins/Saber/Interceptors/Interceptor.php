<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/25
 * Time: 9:25
 */

namespace ESD\Plugins\Saber\Interceptors;

/**
 * 拦截器实例
 * Class Interceptor
 * @package ESD\Plugins\Saber
 */
abstract class Interceptor
{
    const BEFORE = "before";
    const AFTER = "after";
    const RETRY = "retry";
    const BEFORE_REDIRECT = "before_redirect";
    /**
     * 拦截器类型
     * @var string
     */
    private $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    abstract public function getName(): string;
}